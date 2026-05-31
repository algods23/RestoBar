@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Reports</h1>
    <a href="{{ route('reports.pdf') }}" class="btn btn-dark">Export PDF</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card p-3"><div class="text-muted">Daily Sales</div><div class="h4 mb-0">₱{{ number_format($daily['sales'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div class="text-muted">Weekly Sales</div><div class="h4 mb-0">₱{{ number_format($weekly['sales'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div class="text-muted">Monthly Sales</div><div class="h4 mb-0">₱{{ number_format($monthly['sales'], 2) }}</div></div></div>
</div>

<div class="card p-3">
    <h2 class="h5">Best Selling Items</h2>
    <table class="table align-middle">
        <thead><tr><th>Product</th><th>Qty Sold</th><th>Sales</th></tr></thead>
        <tbody>
            @foreach ($bestSellingItems as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->total_quantity }}</td>
                    <td>₱{{ number_format($item->total_sales, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
