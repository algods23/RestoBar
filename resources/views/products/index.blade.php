@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Products</h1>
    <a href="{{ route('products.create') }}" class="btn btn-dark">Add Product</a>
</div>

<div class="card p-3">
    <table class="table align-middle">
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                    <td><img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" style="width:56px;height:40px;object-fit:cover;border-radius:8px;"></td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name }}</td>
                    <td>₱{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ ucfirst($product->status) }}</td>
                    <td class="text-end">
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $products->links() }}
</div>
@endsection
