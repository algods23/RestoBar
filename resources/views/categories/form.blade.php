@extends('layouts.app')

@section('content')
<div class="card p-4 mx-auto" style="max-width: 760px;">
    <h1 class="h4 mb-3">{{ $category->exists ? 'Edit Category' : 'Add Category' }}</h1>
    <form method="POST" action="{{ $action }}">
        @csrf
        @if($method !== 'POST') @method($method) @endif
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
        </div>
        <button class="btn btn-dark">Save</button>
    </form>
</div>
@endsection
