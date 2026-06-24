@extends('layouts.app')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h1 class="h5">Stock Adjustment</h1>
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Product</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Select a product...</option>
                        @foreach($adjustmentProducts as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="stock_in">Stock In</option>
                        <option value="stock_out">Stock Out</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Quantity</label>
                    <input name="quantity" type="number" min="1" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Notes</label>
                    <input name="notes" class="form-control">
                </div>
                <button class="btn btn-dark w-100">Save</button>
            </form>
        </div>

        <div class="card p-3 mb-3">
            <h2 class="h6">Low Stock Products</h2>
            @forelse ($lowStockProducts as $product)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ $product->name }}</span>
                    <strong>{{ $product->stock }}</strong>
                </div>
            @empty
                <div class="text-muted py-2">No low stock products.</div>
            @endforelse
        </div>

        <div class="card p-3">
            <h2 class="h6">Inventory Logs</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>New</th></tr></thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->product?->name }}</td>
                                <td>{{ $log->type }}</td>
                                <td>{{ $log->quantity }}</td>
                                <td>{{ $log->new_stock }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $logs->links('pagination.default') }}
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-3">
            <h2 class="h5">Product Availability</h2>
            <form action="{{ route('inventory.index') }}" method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search product name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="availability" class="form-select">
                        <option value="">All Status</option>
                        <option value="available" @selected(request('availability') === 'available')>Available</option>
                        <option value="low" @selected(request('availability') === 'low')>Low Stock</option>
                        <option value="out" @selected(request('availability') === 'out')>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-fill">Filter</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-end">Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>
                                <td class="text-end">
                                    <strong>{{ $product->stock }}</strong>
                                    <div class="small text-muted">Reorder: {{ $product->reorder_level }}</div>
                                </td>
                                <td>
                                    @if ($product->stock <= 0)
                                        <span class="badge bg-danger">Out</span>
                                    @elseif ($product->isLowStock())
                                        <span class="badge bg-warning text-dark">Low</span>
                                    @else
                                        <span class="badge bg-success">Available</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $products->links('pagination.default') }}
        </div>
    </div>
</div>
@endsection
