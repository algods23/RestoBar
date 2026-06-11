@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Orders</h1>
<div class="card p-3">
    <table class="table align-middle">
        <thead><tr><th>ID</th><th>Customer</th><th>Table</th><th>Type</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->customer_name ?: '—' }}</td>
                    <td>{{ $order->tables->count() ? $order->tables->pluck('number')->map(fn($t) => 'T'.$t)->join(', ') : '—' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
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
