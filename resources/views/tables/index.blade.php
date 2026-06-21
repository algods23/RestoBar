@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Tables</h1>
    <a href="{{ route('tables.create') }}" class="btn btn-dark">Add Table</a>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card p-3">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Table</th>
                <th>Status</th>
                <th>Current Order</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tables as $table)
                <tr>
                    <td>T{{ $table->number }}</td>
                    <td>
                        @if ($table->is_occupied)
                            <span class="badge text-bg-danger">Occupied</span>
                        @else
                            <span class="badge text-bg-success">Available</span>
                        @endif
                    </td>
                    <td>
                        @if ($table->current_order_id)
                            <a href="{{ route('orders.show', $table->current_order_id) }}">#{{ $table->current_order_id }}</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('tables.edit', $table) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="{{ route('tables.destroy', $table) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" {{ $table->is_occupied ? 'disabled' : '' }}>Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No tables yet. Add your first table.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $tables->links('pagination.default') }}
</div>
@endsection
