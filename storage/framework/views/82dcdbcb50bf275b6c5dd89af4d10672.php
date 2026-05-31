

<?php $__env->startSection('content'); ?>
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-1">Order #<?php echo e($order->id); ?></h1>
            <div class="text-muted"><?php echo e($order->created_at->format('M d, Y h:i A')); ?></div>
        </div>
        <div>
            <a href="<?php echo e(route('orders.receipt', $order)); ?>" class="btn btn-outline-dark">Print Receipt</a>
            <?php if($order->status === 'pending'): ?>
                <button class="btn btn-success pay-btn" data-id="<?php echo e($order->id); ?>" data-total="<?php echo e($order->total_amount); ?>">Pay</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><strong>Cashier:</strong> <?php echo e($order->user?->name); ?></div>
        <div class="col-md-4"><strong>Type:</strong> <?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></div>
        <div class="col-md-4"><strong>Status:</strong> <?php echo e(ucfirst($order->status)); ?></div>
    </div>

    <table class="table">
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product?->name); ?></td>
                    <td><?php echo e($item->quantity); ?></td>
                    <td>₱<?php echo e(number_format($item->price, 2)); ?></td>
                    <td>₱<?php echo e(number_format($item->subtotal, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="ms-auto" style="max-width: 320px;">
        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>₱<?php echo e(number_format($order->subtotal, 2)); ?></strong></div>
        <div class="d-flex justify-content-between"><span>Discount</span><strong>₱<?php echo e(number_format($order->discount_amount, 2)); ?></strong></div>
        <div class="d-flex justify-content-between"><span>VAT</span><strong>₱<?php echo e(number_format($order->vat_amount, 2)); ?></strong></div>
        <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>₱<?php echo e(number_format($order->total_amount, 2)); ?></strong></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/orders/show.blade.php ENDPATH**/ ?>