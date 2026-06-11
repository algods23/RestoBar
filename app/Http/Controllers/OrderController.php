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
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('order_type'), fn ($query) => $query->where('order_type', $request->string('order_type')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'user');
        $products = Product::where('status', 'available')->orderBy('name')->get();

        return view('orders.show', compact('order', 'products'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,cancelled'],
        ]);

        $order->update($validated);

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

        // Create payment record
        $order->payments()->create([
            'user_id' => $request->user()->id,
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Mark order as completed
        $order->update(['status' => Order::STATUS_COMPLETED]);

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
            'item_type'  => 'required|in:dine_in,takeout',
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

        return back()->with('success', 'Item added to order.');
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
        // Vat is 12% of subtotal - discount
        // But do we know if vat is enabled for this order?
        // Wait, the order has a vat_amount. If it was > 0, we can recalculate it.
        // Let's assume VAT is enabled if it originally had VAT, or we just recalculate based on subtotal.
        // Actually, let's keep discount as is.
        $discountAmount = $order->discount_amount;
        $vatAmount = $order->vat_amount > 0 ? round(($subtotal - $discountAmount) * 0.12, 2) : 0;
        $total = max(0, round($subtotal - $discountAmount + $vatAmount, 2));

        $order->update([
            'subtotal'     => $subtotal,
            'vat_amount'   => $vatAmount,
            'total_amount' => $total,
            // Re-evaluate order type if needed? Keep it mixed or unchanged for now.
        ]);
    }
}
