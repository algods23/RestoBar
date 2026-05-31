@extends('layouts.app')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Sales Today</div><div class="h3 mb-0">₱{{ number_format($totalSalesToday, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Orders Today</div><div class="h3 mb-0">{{ $ordersToday }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">This Week</div><div class="h3 mb-0">₱{{ number_format($totalSalesThisWeek, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">This Month</div><div class="h3 mb-0">₱{{ number_format($totalSalesThisMonth, 2) }}</div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted">Low Stock Items</div><div class="h3 mb-0">{{ $lowStockProducts }}</div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Sales Overview</h2>
            </div>
            <canvas id="salesChart" height="110"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-3 h-100">
            <h2 class="h5">Best Selling Items</h2>
            <div class="list-group list-group-flush">
                @foreach ($bestSellingItems as $item)
                    <div class="list-group-item d-flex justify-content-between">
                        <span>{{ $item->product?->name }}</span>
                        <strong>{{ $item->total_quantity }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card p-3 mt-3">
    <h2 class="h5">Recent Orders</h2>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>ID</th><th>Type</th><th>Status</th><th>Total</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach ($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>₱{{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_keys($salesChart)) !!},
        datasets: [{
            label: 'Sales',
            data: {!! json_encode(array_values($salesChart)) !!},
            borderColor: '#111827',
            backgroundColor: 'rgba(17,24,39,.12)',
            tension: .35,
            fill: true
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
@endpush
