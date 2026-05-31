

<?php $__env->startSection('content'); ?>
<div class="card p-4 mx-auto" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Receipt #<?php echo e($order->id); ?></h1>
            <div class="text-muted small"><?php echo e($order->created_at->format('M d, Y h:i A')); ?></div>
        </div>
        <button class="btn btn-dark" onclick="window.print()">Print</button>
    </div>

    <div class="row mb-3">
        <div class="col-md-6"><strong>Cashier:</strong> <?php echo e($order->user?->name); ?></div>
        <div class="col-md-6 text-md-end"><strong>Order Type:</strong> <?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></div>
    </div>

    <div class="table-responsive">
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
    </div>

    <div class="ms-auto" style="max-width: 300px;">
        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>₱<?php echo e(number_format($order->subtotal, 2)); ?></strong></div>
        <div class="d-flex justify-content-between"><span>Discount</span><strong>₱<?php echo e(number_format($order->discount_amount, 2)); ?></strong></div>
        <div class="d-flex justify-content-between"><span>VAT</span><strong>₱<?php echo e(number_format($order->vat_amount, 2)); ?></strong></div>
        <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>₱<?php echo e(number_format($order->total_amount, 2)); ?></strong></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.addEventListener('load', () => {
    setTimeout(() => window.print(), 300);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/pos/receipt.blade.php ENDPATH**/ ?>