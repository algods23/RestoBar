@extends('layouts.app')

@section('content')
<div class="card p-4 mx-auto" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Receipt #{{ $order->id }}</h1>
            <div class="text-muted small">{{ $order->created_at->format('M d, Y h:i A') }}</div>
        </div>
        <button class="btn btn-dark" onclick="window.print()">Print</button>
    </div>

    <div class="row mb-3">
        <div class="col-md-6"><strong>Cashier:</strong> {{ $order->user?->name }}</div>
        <div class="col-md-6 text-md-end"><strong>Order Type:</strong> {{ str_replace('_', ' ', ucfirst($order->order_type)) }}</div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>₱{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="ms-auto" style="max-width: 300px;">
        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>₱{{ number_format($order->subtotal, 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>Discount</span><strong>₱{{ number_format($order->discount_amount, 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>VAT</span><strong>₱{{ number_format($order->vat_amount, 2) }}</strong></div>
        <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>₱{{ number_format($order->total_amount, 2) }}</strong></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', () => {
    setTimeout(() => window.print(), 300);
});
</script>
@endpush
