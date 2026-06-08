@extends('layouts.app')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h1 class="h5">Stock Adjustment</h1>
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Product ID</label>
                    <input name="product_id" type="number" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="stock_in">Stock In</option>
                        <option value="stock_out">Stock Out</option>
                        <option value="adjustment">Adjustment</option>
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

        <div class="card p-3">
            <h2 class="h6">Low Stock Products</h2>
            @foreach ($lowStockProducts as $product)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ $product->name }}</span>
                    <strong>{{ $product->stock }}</strong>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-3">
            <h2 class="h5">Inventory Logs</h2>
            <table class="table align-middle">
                <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Previous</th><th>New</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->product?->name }}</td>
                            <td>{{ $log->type }}</td>
                            <td>{{ $log->quantity }}</td>
                            <td>{{ $log->previous_stock }}</td>
                            <td>{{ $log->new_stock }}</td>
                            <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $logs->links('pagination.default') }}
        </div>
    </div>
</div>
@endsection
