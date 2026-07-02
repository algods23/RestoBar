<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with(['user', 'tables'])
            ->when($request->filled('search'), fn ($query) => $query->where('customer_name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')), fn ($query) => $query->where('status', '!=', Order::STATUS_COMPLETED))
            ->when($request->filled('order_type'), fn ($query) => $query->where('order_type', $request->string('order_type')))
            ->when($request->filled('from'), function ($query) use ($request) {
                try {
                    return $query->where('created_at', '>=', \Illuminate\Support\Carbon::parse($request->string('from'))->toDateTimeString());
                } catch (\Exception $e) {
                    return $query;
                }
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                try {
                    return $query->where('created_at', '<=', \Illuminate\Support\Carbon::parse($request->string('to'))->toDateTimeString());
                } catch (\Exception $e) {
                    return $query;
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function archived(Request $request): View
    {
        $orders = Order::with(['user', 'tables'])
            ->where('status', 'completed')
            ->when($request->filled('search'), fn ($query) => $query->where('customer_name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('order_type'), fn ($query) => $query->where('order_type', $request->string('order_type')))
            ->when($request->filled('from'), function ($query) use ($request) {
                try {
                    return $query->where('created_at', '>=', \Illuminate\Support\Carbon::parse($request->string('from'))->toDateTimeString());
                } catch (\Exception $e) {
                    return $query;
                }
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                try {
                    return $query->where('created_at', '<=', \Illuminate\Support\Carbon::parse($request->string('to'))->toDateTimeString());
                } catch (\Exception $e) {
                    return $query;
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.archived', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'user');
        $products = Product::where('status', 'available')->orderBy('name')->get();
        $originalItems = $order->items->where('is_additional', false);
        $additionalItems = $order->items->where('is_additional', true);
        $hasAdditionalItems = $additionalItems->isNotEmpty();

        return view('orders.show', compact('order', 'products', 'hasAdditionalItems', 'originalItems', 'additionalItems'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,cancelled'],
        ]);

        $order->update($validated);

        if (in_array($validated['status'], [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED], true)) {
            $order->releaseTables();
        }

        return back()->with('success', 'Order status updated.');
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {

        $validated = $request->validate([
            'method' => ['required', 'in:cash,card,gcash,bank_transfer'],
            'amount' => ['required', 'numeric', 'min:0'],
            // reference required when method is not cash
            'reference' => ['required_unless:method,cash', 'nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ((float) $validated['amount'] < (float) $order->total_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Amount received is less than the order total.',
            ]);
        }

        DB::transaction(function () use ($order, $request, $validated) {
            $amountReceived = (float) $validated['amount'];
            $saleAmount = (float) $order->total_amount;
            $changeAmount = max(0, $amountReceived - $saleAmount);
            $notes = trim((string) ($validated['notes'] ?? ''));

            if ($changeAmount > 0) {
                $notes = trim($notes . ' Tendered: ' . number_format($amountReceived, 2) . '; Change: ' . number_format($changeAmount, 2));
            }

            $order->payments()->create([
                'user_id' => $request->user()->id,
                'method' => $validated['method'],
                'amount' => $saleAmount,
                'reference' => $validated['reference'] ?? null,
                'notes' => $notes !== '' ? $notes : null,
            ]);

            $order->update([
                'status' => Order::STATUS_COMPLETED,
                'payment_method' => $validated['method'],
            ]);
            $order->releaseTables();
        });

        return back()->with('success', 'Payment recorded and order completed.');
    }

    public function addItem(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Cannot add items to a non-pending order.');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'item_type'  => 'required|in:dine_in,takeout,delivery',
        ]);

        DB::transaction(function () use ($validated, $order, $request) {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            
            if ($product->stock < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Not enough stock. Only {$product->stock} available."
                ]);
            }

            $previousStock = $product->stock;
            $product->decrement('stock', $validated['quantity']);
            
            if ($product->stock <= 0) {
                $product->update(['status' => 'out_of_stock']);
            }

            $orderItem = OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $validated['quantity'],
                'price'      => $product->price,
                'subtotal'   => $product->price * $validated['quantity'],
                'item_type'  => $validated['item_type'],
                'is_additional' => true,
            ]);

            Inventory::create([
                'product_id'     => $product->id,
                'user_id'        => $request->user()->id,
                'order_id'       => $order->id,
                'type'           => 'deduction',
                'quantity'       => $validated['quantity'],
                'previous_stock' => $previousStock,
                'new_stock'      => $product->stock,
                'notes'          => 'Added to existing order #' . $order->id,
            ]);

            $this->recalculateOrder($order);
        });

        return back()
            ->with('success', 'Item added to order.')
            ->with('additional_kitchen_receipt_url', route('orders.kitchen_receipt', ['order' => $order, 'additional' => 1]));
    }

    public function removeItem(Order $order, OrderItem $item): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Cannot remove items from a non-pending order.');
        }

        if ($item->order_id !== $order->id) {
            return back()->with('error', 'Item does not belong to this order.');
        }

        DB::transaction(function () use ($order, $item) {
            $product = Product::lockForUpdate()->findOrFail($item->product_id);
            $previousStock = $product->stock;
            
            $product->increment('stock', $item->quantity);
            
            if ($product->status === 'out_of_stock' && $product->stock > 0) {
                $product->update(['status' => 'available']);
            }

            Inventory::create([
                'product_id'     => $product->id,
                'user_id'        => auth()->id(),
                'order_id'       => $order->id,
                'type'           => 'stock_in',
                'quantity'       => $item->quantity,
                'previous_stock' => $previousStock,
                'new_stock'      => $product->stock,
                'notes'          => 'Removed from order #' . $order->id,
            ]);

            $item->delete();
            $this->recalculateOrder($order);
        });

        return back()->with('success', 'Item removed from order.');
    }

    private function recalculateOrder(Order $order): void
    {
        $subtotal = $order->items()->sum('subtotal');
        $discountPercentage = (float) $order->discount_percentage;
        $discountAmount = round($subtotal * ($discountPercentage / 100), 2);
        $total = max(0, round($subtotal - $discountAmount, 2));

        $order->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'vat_amount'      => 0,
            'total_amount'    => $total,
        ]);
    }
}
