<?php $__env->startSection('content'); ?>
<h1 class="h4 mb-3">Archived Orders</h1>
<div class="card p-3">
    <table class="table align-middle">
        <thead><tr><th>ID</th><th>Customer</th><th>Table</th><th>Type</th><th>Status</th><th>Total</th><th>Time</th><th></th></tr></thead>
        <tbody>
            <?php
                $lastDate = null;
            ?>
            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $currentDate = $order->created_at->format('F d, Y');
                ?>
                <?php if($currentDate !== $lastDate): ?>
                    <?php $lastDate = $currentDate; ?>
                    <tr class="table-group-header">
                        <td colspan="8" class="fw-semibold py-2 px-3 text-dark bg-light" style="font-size: 0.85rem; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">
                            <?php echo e(strtoupper($currentDate)); ?>

                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>#<?php echo e($order->id); ?></td>
                    <td><?php echo e($order->customer_name ?: '—'); ?></td>
                    <td><?php echo e($order->tables->count() ? $order->tables->pluck('number')->map(fn($t) => 'T'.$t)->join(', ') : '—'); ?></td>
                    <td><?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded"><?php echo e(ucfirst($order->status)); ?></span></td>
                    <td>₱<?php echo e(number_format($order->total_amount, 2)); ?></td>
                    <td><?php echo e($order->created_at->format('h:i A')); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-sm btn-outline-dark">View</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($orders->links('pagination.default')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/orders/archived.blade.php ENDPATH**/ ?>