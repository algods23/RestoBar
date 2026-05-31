<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $logs = Inventory::with(['product', 'user', 'order'])->latest()->paginate(20);
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'reorder_level')
            ->orderBy('name')
            ->get();

        return view('inventory.index', compact('logs', 'lowStockProducts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:stock_in,stock_out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $previousStock = $product->stock;
            $delta = $validated['type'] === 'stock_out' ? -$validated['quantity'] : $validated['quantity'];

            $product->update(['stock' => max(0, $product->stock + $delta)]);

            if ($product->stock <= 0) {
                $product->update(['status' => 'out_of_stock']);
            }

            Inventory::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $product->stock,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Inventory adjusted.');
    }
}
