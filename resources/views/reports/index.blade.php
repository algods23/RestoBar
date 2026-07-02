@extends('layouts.app')

@section('content')
@php
    $isSales = $reportType === 'sales';
    $activeButton = fn ($type) => $reportType === $type ? 'btn-dark' : 'btn-outline-dark';
@endphp

<div class="d-flex flex-column flex-xl-row gap-3 justify-content-between align-items-xl-center mb-3">
    <div>
        <h1 class="h4 mb-1">Reports</h1>
        <div class="text-muted small">{{ $from->format('M d, Y') }} to {{ $to->format('M d, Y') }}</div>
    </div>
    <a
        href="{{ route('reports.excel', ['type' => $reportType, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
        class="btn btn-success"
    >
        Export Excel
    </a>
</div>

<div class="card p-3 mb-4">
    <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold">From</label>
            <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">To</label>
            <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold d-block">Report</label>
            <div class="btn-group w-100" role="group" aria-label="Report type">
                <button type="submit" name="type" value="sales" class="btn {{ $activeButton('sales') }}">Sales</button>
                <button type="submit" name="type" value="inventory" class="btn {{ $activeButton('inventory') }}">Inventory</button>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" name="type" value="{{ $reportType }}" class="btn btn-primary w-100">Generate</button>
        </div>
    </form>
</div>

@if($isSales)
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card p-3"><div class="text-muted">Total Sales</div><div class="h4 mb-0">&#8369;{{ number_format($summary['sales'], 2) }}</div></div></div>
        <div class="col-md-4"><div class="card p-3"><div class="text-muted">Orders</div><div class="h4 mb-0">{{ number_format($summary['orders']) }}</div></div></div>
        <div class="col-md-4"><div class="card p-3"><div class="text-muted">Subtotal</div><div class="h4 mb-0">&#8369;{{ number_format($summary['subtotal'], 2) }}</div></div></div>
    </div>

    <div class="card p-3 mb-4">
        <h2 class="h5">Best Selling Items</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Product</th><th>Qty Sold</th><th>Sales</th></tr></thead>
                <tbody>
                    @forelse ($bestSellingItems as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->total_quantity }}</td>
                            <td>&#8369;{{ number_format($item->total_sales, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No sales found for this date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-3">
        <h2 class="h5">Sales Details</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr><th>Date/Time</th><th>Order</th><th>Cashier</th><th>Type</th><th class="text-end">Subtotal</th><th class="text-end">Discount</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="text-nowrap">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user?->name ?? 'N/A' }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</td>
                            <td class="text-end">&#8369;{{ number_format($order->subtotal, 2) }}</td>
                            <td class="text-end">&#8369;{{ number_format($order->discount_amount, 2) }}</td>
                            <td class="text-end fw-semibold">&#8369;{{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No completed orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Logs</div><div class="h4 mb-0">{{ number_format($summary['logs']) }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Stock In</div><div class="h4 mb-0">{{ number_format($summary['stock_in']) }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Stock Out</div><div class="h4 mb-0">{{ number_format($summary['stock_out']) }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Adjustments</div><div class="h4 mb-0">{{ number_format($summary['adjustments']) }}</div></div></div>
    </div>

    <div class="card p-3">
        <h2 class="h5">Inventory Details</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr><th>Date/Time</th><th>User</th><th>Product</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Previous</th><th class="text-end">New</th><th>Notes</th></tr>
                </thead>
                <tbody>
                    @forelse($inventoryLogs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $log->user?->name ?? 'N/A' }}</td>
                            <td>{{ $log->product?->name ?? 'N/A' }}</td>
                            <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', $log->type) }}</span></td>
                            <td class="text-end">{{ $log->quantity }}</td>
                            <td class="text-end">{{ $log->previous_stock }}</td>
                            <td class="text-end fw-semibold">{{ $log->new_stock }}</td>
                            <td>{{ $log->notes }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">No inventory activity found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
