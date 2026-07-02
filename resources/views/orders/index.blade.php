@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Orders</h1>
<div class="card p-3">
    <form method="GET" action="{{ url()->current() }}" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">Search Customer</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">From Date & Time</label>
            <input type="datetime-local" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
        </div>
        <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">To Date & Time</label>
            <input type="datetime-local" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
        </div>
        <div class="col-md-3 col-sm-12 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>
            @if(request()->filled('from') || request()->filled('to') || request()->filled('search'))
                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </div>
    </form>

    <table class="table align-middle">
        <thead><tr><th>ID</th><th>Customer</th><th>Table</th><th>Type</th><th>Status</th><th>Total</th><th>Time</th><th></th></tr></thead>
        <tbody>
            @php
                $lastDate = null;
            @endphp
            @foreach ($orders as $order)
                @php
                    $currentDate = $order->created_at->format('F d, Y');
                @endphp
                @if ($currentDate !== $lastDate)
                    @php $lastDate = $currentDate; @endphp
                    <tr class="table-group-header">
                        <td colspan="8" class="fw-semibold py-2 px-3 text-dark bg-light" style="font-size: 0.85rem; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">
                            {{ strtoupper($currentDate) }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->customer_name ?: '—' }}</td>
                    <td>{{ $order->tables->count() ? $order->tables->pluck('number')->map(fn($t) => 'T'.$t)->join(', ') : '—' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->created_at->format('h:i A') }}</td>
                    <td class="text-end">
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-dark">View</a>
                        @if($order->status === 'pending')
                            <button class="btn btn-sm btn-success pay-btn ms-1" data-id="{{ $order->id }}" data-total="{{ $order->total_amount }}">Pay</button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links('pagination.default') }}
</div>
@include('orders.partials.payment-modal')
@endsection
