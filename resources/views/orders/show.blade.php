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
    showPaymentModal(id, total);
});

function showPaymentModal(orderId, total) {
        let modal = document.getElementById('paymentModal');
        if (!modal) {
                modal = document.createElement('div');
                modal.innerHTML = `
                        <div class="modal fade" id="paymentModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <form id="paymentForm">
                                            <input type="hidden" name="order_id" id="pm_order_id">
                                            <div class="mb-2"><label class="form-label">Payment Method</label>
                                                <select name="method" id="pm_method" class="form-select">
                                                    <option value="cash">Cash</option>
                                                    <option value="card">Card</option>
                                                    <option value="gcash">GCash</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                </select>
                                            </div>
                                            <div class="mb-2"><label class="form-label">Amount Paid</label>
                                                <input type="number" step="0.01" min="0" name="amount" id="pm_amount" class="form-control">
                                            </div>
                                            <div class="mb-2" id="pm_reference_row" style="display:none"><label class="form-label">Reference</label>
                                                <input type="text" name="reference" id="pm_reference" class="form-control">
                                            </div>
                                            <div class="mb-2"><label class="form-label">Notes</label>
                                                <input type="text" name="notes" id="pm_notes" class="form-control">
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button id="pm_submit" type="button" class="btn btn-primary">Record Payment</button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                document.body.appendChild(modal);

                const pmMethod = document.getElementById('pm_method');
                const pmReferenceRow = document.getElementById('pm_reference_row');
                const pmReference = document.getElementById('pm_reference');

                // show/hide reference field depending on method
                pmMethod.addEventListener('change', () => {
                    if (pmMethod.value === 'cash') {
                        pmReferenceRow.style.display = 'none';
                        pmReference.removeAttribute('required');
                    } else {
                        pmReferenceRow.style.display = '';
                        pmReference.setAttribute('required', 'required');
                    }
                });

                document.getElementById('pm_submit').addEventListener('click', async () => {
                        const orderId = document.getElementById('pm_order_id').value;
                        const method = document.getElementById('pm_method').value;
                        const amount = document.getElementById('pm_amount').value;
                    const reference = document.getElementById('pm_reference').value;
                    const notes = document.getElementById('pm_notes').value;

                        const token = document.querySelector('meta[name="csrf-token"]').content;

                        const res = await fetch(`/orders/${orderId}/pay`, {
                                method: 'POST',
                                headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': token,
                                        'Accept': 'application/json'
                                },
                                body: JSON.stringify({ method, amount, reference, notes })
                        });

                        if (res.ok) {
                                location.reload();
                        } else {
                                const payload = await res.json().catch(() => ({}));
                                alert(payload.message || 'Failed to record payment');
                        }
                });
        }

        // set values and show modal
        document.getElementById('pm_order_id').value = orderId;
        document.getElementById('pm_amount').value = total.toFixed(2);

        const bsModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        bsModal.show();
}
</script>
@endpush
