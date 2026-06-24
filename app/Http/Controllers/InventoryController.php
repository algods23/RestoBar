<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $logs = Inventory::with(['product', 'user', 'order'])
            ->latest()
            ->paginate(10, ['*'], 'log_page')
            ->appends($request->except('log_page'));

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'reorder_level')
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();
        $adjustmentProducts = Product::orderBy('name')->get();
        $products = Product::with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('availability'), function ($query) use ($request) {
                match ($request->availability) {
                    'out' => $query->where('stock', '<=', 0),
                    'low' => $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'reorder_level'),
                    'available' => $query->whereColumn('stock', '>', 'reorder_level'),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(20, ['*'], 'product_page')
            ->appends($request->except('product_page'));

        return view('inventory.index', compact('logs', 'lowStockProducts', 'products', 'adjustmentProducts', 'categories'));
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
