@extends('layouts.app')

@section('content')
<div class="card p-4 mx-auto" style="max-width: 900px;">
    <h1 class="h4 mb-3">{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h1>
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        @if($method !== 'POST') @method($method) @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Barcode</label>
                <input name="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Image</label>
                <input name="image" type="file" accept="image/*" class="form-control">
                <div class="form-text">Upload a product photo for POS cards.</div>
                @if($product->image)
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="mt-2 rounded border" style="width: 140px; height: 100px; object-fit: cover;">
                @endif
            </div>
            <div class="col-md-3">
                <label class="form-label">Price</label>
                <input name="price" type="number" step="0.01" class="form-control" value="{{ old('price', $product->price) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Stock</label>
                <input name="stock" type="number" min="0" class="form-control" value="{{ old('stock', $product->stock) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Reorder Level</label>
                <input name="reorder_level" type="number" min="0" class="form-control" value="{{ old('reorder_level', $product->reorder_level) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" @selected(old('status', $product->status) === 'available')>Available</option>
                    <option value="out_of_stock" @selected(old('status', $product->status) === 'out_of_stock')>Out of Stock</option>
                    <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>
        <button class="btn btn-dark mt-3">Save</button>
    </form>
</div>
@endsection
