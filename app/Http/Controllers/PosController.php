<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $cart = $this->cart();
        $products = Product::with('category')
            ->where('status', 'available')
            ->orderBy('name')
            ->get();
        $categories = Category::whereHas('products', function ($query) {
                $query->where('status', 'available');
            })
            ->orderBy('name')
            ->get();
        $tables = Table::orderBy('number')->get();

        return view('pos.index', [
            'cart'       => $cart,
            'products'   => $products,
            'categories' => $categories,
            'tables'     => $tables,
            'totals'     => $this->totals($cart),
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->string('query')->toString();
        $categoryId = $request->integer('category_id');

        $products = Product::with('category')
            ->where('status', 'available')
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($builder) use ($query) {
                    $builder->where('name', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%");
                });
            })
            ->when($categoryId > 0, function ($builder) use ($categoryId) {
                $builder->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['nullable', 'integer', 'min:1'],
            'item_type'  => ['nullable', 'in:dine_in,takeout,delivery'],
        ]);

        $product  = Product::findOrFail($validated['product_id']);
        $quantity = $validated['quantity'] ?? 1;
        $itemType = $validated['item_type'] ?? 'dine_in';
        $cartKey  = "{$product->id}_{$itemType}";
        $cart     = $this->cart();

        // Stock check across ALL rows for this product
        $totalQty = collect($cart)
            ->filter(fn($i) => $i['product_id'] == $product->id)
            ->sum('quantity');

        if ($totalQty + $quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock available.',
            ]);
        }

        $cart[$cartKey] = [
            'product_id' => $product->id,
            'item_type'  => $itemType,
            'name'       => $product->name,
            'barcode'    => $product->barcode,
            'price'      => (float) $product->price,
            'quantity'   => ($cart[$cartKey]['quantity'] ?? 0) + $quantity,
            'stock'      => (int) $product->stock,
        ];

        $this->storeCart($cart);

        return response()->json($this->cartPayload());
    }

    public function updateCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'    => ['required', 'exists:products,id'],
            'item_type'     => ['nullable', 'in:dine_in,takeout,delivery'],
            'new_item_type' => ['nullable', 'in:dine_in,takeout,delivery'],
            'quantity'      => ['required', 'integer', 'min:0'],
        ]);

        $cart        = $this->cart();
        $product     = Product::findOrFail($validated['product_id']);
        $itemType    = $validated['item_type'] ?? 'dine_in';
        $newItemType = $validated['new_item_type'] ?? $itemType;
        $cartKey     = "{$product->id}_{$itemType}";
        $newCartKey  = "{$product->id}_{$newItemType}";

        if (! isset($cart[$cartKey])) {
            return response()->json($this->cartPayload());
        }

        // Type change: merge old row into new type row
        if ($newCartKey !== $cartKey) {
            $mergedQty = ($cart[$newCartKey]['quantity'] ?? 0) + $cart[$cartKey]['quantity'];
            $otherQty  = collect($cart)
                ->filter(fn($i, $k) => $i['product_id'] == $product->id && $k !== $cartKey && $k !== $newCartKey)
                ->sum('quantity');
            if ($otherQty + $mergedQty > $product->stock) {
                throw ValidationException::withMessages(['quantity' => 'Not enough stock available.']);
            }
            $cart[$newCartKey] = array_merge($cart[$cartKey], [
                'item_type' => $newItemType,
                'quantity'  => $mergedQty,
            ]);
            unset($cart[$cartKey]);
        } elseif ($validated['quantity'] === 0) {
            unset($cart[$cartKey]);
        } else {
            $otherQty = collect($cart)
                ->filter(fn($i, $k) => $i['product_id'] == $product->id && $k !== $cartKey)
                ->sum('quantity');
            if ($otherQty + $validated['quantity'] > $product->stock) {
                throw ValidationException::withMessages(['quantity' => 'Requested quantity exceeds stock.']);
            }
            $cart[$cartKey]['quantity'] = $validated['quantity'];
        }

        $this->storeCart($cart);
        return response()->json($this->cartPayload());
    }

    public function removeCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'item_type'  => ['nullable', 'in:dine_in,takeout,delivery'],
        ]);

        $itemType = $validated['item_type'] ?? 'dine_in';
        $cartKey  = "{$validated['product_id']}_{$itemType}";
        $cart     = $this->cart();
        unset($cart[$cartKey]);
        $this->storeCart($cart);

        return response()->json($this->cartPayload());
    }

    public function clearCart(): JsonResponse
    {
        $this->storeCart([]);

        return response()->json($this->cartPayload());
    }

    public function checkout(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'order_type'        => ['required', 'in:dine_in,takeout,mixed,delivery'],
            'discount_amount'   => ['nullable', 'numeric', 'min:0'],
            'vat_enabled'       => ['nullable', 'boolean'],
            'payment_method'    => ['required', 'in:cash,card,gcash,bank_transfer'],
            'pay_now'           => ['nullable', 'boolean'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'amount_paid'       => ['nullable', 'numeric', 'min:0'],
            'notes'             => ['nullable', 'string', 'max:255'],
            'customer_name'     => ['nullable', 'string', 'max:100'],
            'tables'            => ['nullable', 'array'],
            'tables.*'          => ['integer'],
        ]);

        $cart = $this->cart();

        if ($cart === []) {
            return back()->withErrors(['cart' => 'Cart is empty.']);
        }

        $totals = $this->totals(
            $cart,
            (float) ($validated['discount_amount'] ?? 0),
            $request->boolean('vat_enabled')
        );

        $order = DB::transaction(function () use ($validated, $cart, $totals, $request) {
            $order = Order::create([
                'user_id'         => $request->user()->id,
                'order_type'      => $validated['order_type'],
                'subtotal'        => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'vat_amount'      => $totals['vat_amount'],
                'total_amount'    => $totals['total'],
                'status'          => Order::STATUS_PENDING,
                'payment_method'  => $validated['payment_method'],
                'customer_name'   => $validated['customer_name'] ?? null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            // Mark selected tables as occupied
            if (!empty($validated['tables'])) {
                Table::whereIn('number', $validated['tables'])
                    ->update(['is_occupied' => true, 'current_order_id' => $order->id]);
            }

            foreach ($cart as $cartItem) {
                $product = Product::lockForUpdate()->findOrFail($cartItem['product_id']);

                if ($product->stock < $cartItem['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => "Stock for {$product->name} is no longer sufficient.",
                    ]);
                }

                $lineSubtotal = $cartItem['price'] * $cartItem['quantity'];

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $cartItem['quantity'],
                    'price'      => $cartItem['price'],
                    'subtotal'   => $lineSubtotal,
                    'item_type'  => $validated['order_type'] === 'delivery' ? 'delivery' : ($cartItem['item_type'] ?? 'dine_in'),
                    'is_additional' => false,
                ]);

                $previousStock = $product->stock;
                $product->decrement('stock', $cartItem['quantity']);
                $product->refresh();

                if ($product->stock <= 0) {
                    $product->update(['status' => 'out_of_stock']);
                }

                Inventory::create([
                    'product_id'     => $product->id,
                    'user_id'        => $request->user()->id,
                    'order_id'       => $order->id,
                    'type'           => 'deduction',
                    'quantity'       => $cartItem['quantity'],
                    'previous_stock' => $previousStock,
                    'new_stock'      => $product->stock,
                    'notes'          => 'POS checkout',
                ]);
            }

            return $order;
        });

        if ($request->boolean('pay_now')) {
            if ($validated['payment_method'] !== 'cash' && empty($validated['payment_reference'])) {
                throw ValidationException::withMessages([
                    'payment_reference' => 'Reference is required for non-cash payments.',
                ]);
            }

            $amountPaid = $validated['amount_paid'] ?? $totals['total'];

            if ($amountPaid < $totals['total']) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Amount paid is less than order total.',
                ]);
            }

            $order->payments()->create([
                'user_id'   => $request->user()->id,
                'method'    => $validated['payment_method'],
                'amount'    => $amountPaid,
                'reference' => $validated['payment_reference'] ?? null,
                'notes'     => 'Paid at checkout',
            ]);

            $order->update(['status' => Order::STATUS_COMPLETED]);
            $order->releaseTables();
        }

        $request->session()->forget('pos_cart');

        // If the request expects JSON (AJAX checkout), return the receipt URL
        if ($request->expectsJson() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success'             => true,
                'redirect_url'        => route('orders.receipt', $order),
                'kitchen_receipt_url' => route('orders.kitchen_receipt', $order),
            ]);
        }

        return redirect()->route('orders.receipt', $order)
            ->with('success', 'Transaction recorded.');
    }

    public function receipt(Order $order): View
    {
        $order->load('items.product', 'user', 'tables');

        return view('pos.receipt', compact('order'));
    }

    public function kitchenReceipt(Request $request, Order $order): View
    {
        $order->load([
            'items' => function ($query) use ($request) {
                $query->with('product');

                if ($request->boolean('additional')) {
                    $query->where('is_additional', true);
                }
            },
            'tables',
        ]);

        return view('pos.kitchen_receipt', [
            'order' => $order,
            'additionalOnly' => $request->boolean('additional'),
        ]);
    }

    /**
     * Return occupied tables with their current order info (for Add-to-Order modal).
     */
    public function occupiedTables(): JsonResponse
    {
        $tables = Table::where('is_occupied', true)
            ->whereNotNull('current_order_id')
            ->with(['currentOrder' => fn ($q) => $q->where('status', 'pending')])
            ->orderBy('number')
            ->get()
            ->filter(fn ($t) => $t->currentOrder !== null)
            ->groupBy('current_order_id')
            ->map(function ($group) {
                $order = $group->first()->currentOrder;
                return [
                    'number'        => $group->pluck('number')->implode(', '),
                    'order_id'      => $order->id,
                    'customer_name' => $order->customer_name,
                    'order_total'   => number_format($order->total_amount, 2),
                    'items_count'   => $order->items()->count(),
                ];
            })
            ->values();

        return response()->json($tables);
    }

    /**
     * Add current cart items as add-ons to an existing order.
     */
    public function addToExistingOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $cart = $this->cart();

        if ($cart === []) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $order = Order::find($validated['order_id']);

        if (!$order || $order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'No active order found.'], 422);
        }

        DB::transaction(function () use ($order, $cart, $request) {
            foreach ($cart as $cartItem) {
                $product = Product::lockForUpdate()->findOrFail($cartItem['product_id']);

                if ($product->stock < $cartItem['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => "Stock for {$product->name} is no longer sufficient.",
                    ]);
                }

                $lineSubtotal = $cartItem['price'] * $cartItem['quantity'];

                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $product->id,
                    'quantity'      => $cartItem['quantity'],
                    'price'         => $cartItem['price'],
                    'subtotal'      => $lineSubtotal,
                    'item_type'     => $cartItem['item_type'] ?? 'dine_in',
                    'is_additional' => true,
                ]);

                $previousStock = $product->stock;
                $product->decrement('stock', $cartItem['quantity']);
                $product->refresh();

                if ($product->stock <= 0) {
                    $product->update(['status' => 'out_of_stock']);
                }

                Inventory::create([
                    'product_id'     => $product->id,
                    'user_id'        => $request->user()->id,
                    'order_id'       => $order->id,
                    'type'           => 'deduction',
                    'quantity'       => $cartItem['quantity'],
                    'previous_stock' => $previousStock,
                    'new_stock'      => $product->stock,
                    'notes'          => 'Add-on to order #' . $order->id,
                ]);
            }

            // Recalculate order totals
            $subtotal = $order->items()->sum('subtotal');
            $discountAmount = $order->discount_amount;
            $vatAmount = $order->vat_amount > 0 ? round(($subtotal - $discountAmount) * 0.12, 2) : 0;
            $total = max(0, round($subtotal - $discountAmount + $vatAmount, 2));

            $order->update([
                'subtotal'     => $subtotal,
                'vat_amount'   => $vatAmount,
                'total_amount' => $total,
            ]);
        });

        $request->session()->forget('pos_cart');

        return response()->json([
            'success'             => true,
            'message'             => 'Items added to Order #' . $order->id,
            'kitchen_receipt_url' => route('orders.kitchen_receipt', ['order' => $order, 'additional' => 1]),
        ]);
    }

    private function cart(): array
    {
        return session()->get('pos_cart', []);
    }

    private function storeCart(array $cart): void
    {
        session()->put('pos_cart', $cart);
    }

    private function cartPayload(): array
    {
        $cart = $this->cart();

        return [
            'items'  => array_values($cart),
            'totals' => $this->totals($cart),
        ];
    }

    private function totals(array $cart, float $discountAmount = 0.0, bool $vatEnabled = true): array
    {
        $subtotal  = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $vatAmount = $vatEnabled ? round(($subtotal - $discountAmount) * 0.12, 2) : 0.0;
        $total     = max(0, round($subtotal - $discountAmount + $vatAmount, 2));

        return [
            'subtotal'        => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'vat_amount'      => round($vatAmount, 2),
            'total'           => $total,
        ];
    }
}
