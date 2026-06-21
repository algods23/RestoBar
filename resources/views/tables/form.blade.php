@extends('layouts.app')

@section('content')
<div class="card p-4 mx-auto" style="max-width: 760px;">
    <h1 class="h4 mb-3">{{ $table->exists ? 'Edit Table' : 'Add Table' }}</h1>
    <form method="POST" action="{{ $action }}">
        @csrf
        @if($method !== 'POST') @method($method) @endif
        <div class="mb-3">
            <label class="form-label">Table Number</label>
            <input type="number" min="1" name="number" class="form-control" value="{{ old('number', $table->number) }}" required>
            <div class="form-text">This is the number shown on POS as T1, T2, and so on.</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-dark">Save</button>
            <a href="{{ route('tables.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
