@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Orders</h1>
<div class="card p-3">
    <table class="table align-middle">
        <thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                    <td class="text-end"><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-dark">View</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
</div>
@endsection
