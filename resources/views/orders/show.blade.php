@extends('layouts.app')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-1">Order #{{ $order->id }}</h1>
            <div class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</div>
        </div>
        <div>
            <a href="{{ route('orders.receipt', $order) }}" class="btn btn-outline-dark">Print Receipt</a>
            @if($order->status === 'pending')
                <button class="btn btn-success pay-btn" data-id="{{ $order->id }}" data-total="{{ $order->total_amount }}">Pay</button>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><strong>Cashier:</strong> {{ $order->user?->name }}</div>
        <div class="col-md-4"><strong>Type:</strong> {{ str_replace('_', ' ', ucfirst($order->order_type)) }}</div>
        <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($order->status) }}</div>
    </div>

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

    <div class="ms-auto" style="max-width: 320px;">
        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>₱{{ number_format($order->subtotal, 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>Discount</span><strong>₱{{ number_format($order->discount_amount, 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>VAT</span><strong>₱{{ number_format($order->vat_amount, 2) }}</strong></div>
        <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>₱{{ number_format($order->total_amount, 2) }}</strong></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', event => {
    if (!event.target.classList.contains('pay-btn')) return;
    const id = event.target.dataset.id;
    const total = Number(event.target.dataset.total) || 0;
    // reuse the modal logic from index: create if missing and show
    const showPaymentModal = window.showPaymentModal;
    if (typeof showPaymentModal === 'function') {
        showPaymentModal(id, total);
    } else {
        // fallback: redirect to orders index where modal exists
        window.location.href = '/orders';
    }
});
</script>
@endpush
