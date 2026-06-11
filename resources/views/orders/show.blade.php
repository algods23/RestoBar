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
        <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Price</th><th>Subtotal</th>@if($order->status === 'pending')<th></th>@endif</tr></thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->item_type === 'dine_in' ? '🍽 Dine-in' : '🥡 Takeout' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₱{{ number_format($item->price, 2) }}</td>
                    <td>₱{{ number_format($item->subtotal, 2) }}</td>
                    @if($order->status === 'pending')
                    <td class="text-end">
                        <form action="{{ route('orders.items.remove', [$order, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this item? Stock will be restored.');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($order->status === 'pending')
    <div class="card bg-light p-3 mb-4 border-0">
        <h6 class="mb-2">Add Item to Order</h6>
        <form action="{{ route('orders.items.add', $order) }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-5">
                <label class="form-label small">Product</label>
                <select name="product_id" class="form-select form-select-sm" required>
                    <option value="">Select a product...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (₱{{ $product->price }} | Stock: {{ $product->stock }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Type</label>
                <select name="item_type" class="form-select form-select-sm" required>
                    <option value="dine_in">Dine-in</option>
                    <option value="takeout">Takeout</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Quantity</label>
                <input type="number" name="quantity" class="form-control form-control-sm" min="1" value="1" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-dark w-100">Add Item</button>
            </div>
        </form>
    </div>
    @endif

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
