<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="pm_order_id">
                    <input type="hidden" id="pm_total">
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Order Total</span>
                            <strong id="pm_total_display">&#8369;0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted">Change</span>
                            <strong class="text-success" id="pm_change_display">&#8369;0.00</strong>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Payment Method</label>
                        <select name="method" id="pm_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="gcash">GCash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount Received</label>
                        <input type="number" step="0.01" min="0" name="amount" id="pm_amount" class="form-control" placeholder="Enter amount received">
                    </div>
                    <div class="mb-2" id="pm_reference_row" style="display:none">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" id="pm_reference" class="form-control">
                    </div>
                   
                    <div class="alert alert-warning mb-0">
                        Please confirm the payment details before completing this order.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="pm_submit" type="button" class="btn btn-success">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('2c6dd226-89b6-4d8b-901c-dcbbeea4fe18')): $__env->markAsRenderedOnce('2c6dd226-89b6-4d8b-901c-dcbbeea4fe18'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
function orderPaymentMoney(value) {
    return '\u20b1' + Number(value || 0).toFixed(2);
}

document.addEventListener('click', event => {
    const button = event.target.closest('.pay-btn');
    if (!button) return;

    const total = Number(button.dataset.total) || 0;
    document.getElementById('pm_order_id').value = button.dataset.id;
    document.getElementById('pm_total').value = total.toFixed(2);
    document.getElementById('pm_total_display').textContent = orderPaymentMoney(total);
    document.getElementById('pm_amount').value = '';
    document.getElementById('pm_method').value = 'cash';
    document.getElementById('pm_reference').value = '';
    document.getElementById('pm_notes').value = '';
    document.getElementById('pm_reference_row').style.display = 'none';
    document.getElementById('pm_reference').removeAttribute('required');
    updateOrderPaymentChange();

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
});

function updateOrderPaymentChange() {
    const total = Number(document.getElementById('pm_total').value || 0);
    const amount = Number(document.getElementById('pm_amount').value || 0);
    document.getElementById('pm_change_display').textContent = orderPaymentMoney(Math.max(0, amount - total));
}

document.getElementById('pm_method').addEventListener('change', event => {
    const isCash = event.target.value === 'cash';
    document.getElementById('pm_reference_row').style.display = isCash ? 'none' : '';
    if (isCash) {
        document.getElementById('pm_reference').removeAttribute('required');
    } else {
        document.getElementById('pm_reference').setAttribute('required', 'required');
    }
});

document.getElementById('pm_amount').addEventListener('input', updateOrderPaymentChange);

document.getElementById('pm_submit').addEventListener('click', async () => {
    const submit = document.getElementById('pm_submit');
    const orderId = document.getElementById('pm_order_id').value;
    const total = Number(document.getElementById('pm_total').value || 0);
    const method = document.getElementById('pm_method').value;
    const amount = Number(document.getElementById('pm_amount').value || 0);
    const reference = document.getElementById('pm_reference').value;
    const notes = document.getElementById('pm_notes').value;

    if (amount < total) {
        alert('Amount received is less than the order total.');
        return;
    }

    if (method !== 'cash' && !reference.trim()) {
        alert('Reference is required for non-cash payments.');
        return;
    }

    if (!confirm(`Confirm ${orderPaymentMoney(amount)} ${method.replace('_', ' ')} payment for Order #${orderId}?`)) {
        return;
    }

    submit.disabled = true;
    submit.textContent = 'Recording...';

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
        return;
    }

    const payload = await res.json().catch(() => ({}));
    alert(payload.message || 'Failed to record payment.');
    submit.disabled = false;
    submit.textContent = 'Confirm Payment';
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\RestoBar\resources\views/orders/partials/payment-modal.blade.php ENDPATH**/ ?>