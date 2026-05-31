<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

        return view('pos.index', [
            'cart' => $cart,
            'products' => $products,
            'totals' => $this->totals($cart),
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->string('query')->toString();

        $products = Product::with('category')
            ->where('status', 'available')
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = $validated['quantity'] ?? 1;
        $cart = $this->cart();

        if (($cart[$product->id]['quantity'] ?? 0) + $quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock available.',
            ]);
        }

        $cart[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'price' => (float) $product->price,
            'quantity' => ($cart[$product->id]['quantity'] ?? 0) + $quantity,
            'stock' => (int) $product->stock,
        ];

        $this->storeCart($cart);

        return response()->json($this->cartPayload());
    }

    public function updateCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = $this->cart();
        $product = Product::findOrFail($validated['product_id']);

        if (! isset($cart[$product->id])) {
            return response()->json($this->cartPayload());
        }

        if ($validated['quantity'] === 0) {
            unset($cart[$product->id]);
        } else {
            if ($validated['quantity'] > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Requested quantity exceeds stock.',
                ]);
            }

            $cart[$product->id]['quantity'] = $validated['quantity'];
        }

        $this->storeCart($cart);

        return response()->json($this->cartPayload());
    }

    public function removeCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $cart = $this->cart();
        unset($cart[$validated['product_id']]);
        $this->storeCart($cart);

        return response()->json($this->cartPayload());
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_type' => ['required', 'in:dine_in,takeout,delivery'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'vat_enabled' => ['nullable', 'boolean'],
            'payment_method' => ['required', 'in:cash,card,gcash,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $cart = $this->cart();

        if ($cart === []) {
            return back()->withErrors(['cart' => 'Cart is empty.']);
        }

        $totals = $this->totals($cart, (float) ($validated['discount_amount'] ?? 0), $request->boolean('vat_enabled'));

        $order = DB::transaction(function () use ($validated, $cart, $totals, $request) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_type' => $validated['order_type'],
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'vat_amount' => $totals['vat_amount'],
                'total_amount' => $totals['total'],
                'status' => Order::STATUS_COMPLETED,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cart as $cartItem) {
                $product = Product::lockForUpdate()->findOrFail($cartItem['product_id']);

                if ($product->stock < $cartItem['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => "Stock for {$product->name} is no longer sufficient.",
                    ]);
                }

                $lineSubtotal = $cartItem['price'] * $cartItem['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'subtotal' => $lineSubtotal,
                ]);

                $previousStock = $product->stock;
                $product->decrement('stock', $cartItem['quantity']);
                $product->refresh();

                if ($product->stock <= 0) {
                    $product->update(['status' => 'out_of_stock']);
                }

                Inventory::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'order_id' => $order->id,
                    'type' => 'deduction',
                    'quantity' => $cartItem['quantity'],
                    'previous_stock' => $previousStock,
                    'new_stock' => $product->stock,
                    'notes' => 'POS checkout',
                ]);
            }

            return $order;
        });

        $request->session()->forget('pos_cart');

        return redirect()->route('orders.receipt', $order)->with('success', 'Transaction completed. Receipt ready for printing.');
    }

    public function receipt(Order $order): View
    {
        $order->load('items.product', 'user');

        return view('pos.receipt', compact('order'));
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
            'items' => array_values($cart),
            'totals' => $this->totals($cart),
        ];
    }

    private function totals(array $cart, float $discountAmount = 0.0, bool $vatEnabled = true): array
    {
        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $vatAmount = $vatEnabled ? round(($subtotal - $discountAmount) * 0.12, 2) : 0.0;
        $total = max(0, round($subtotal - $discountAmount + $vatAmount, 2));

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'vat_amount' => round($vatAmount, 2),
            'total' => $total,
        ];
    }
}
