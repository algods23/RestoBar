

<?php $__env->startSection('content'); ?>
<h1 class="h4 mb-3">Orders</h1>
<div class="card p-3">
    <table class="table align-middle">
        <thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>#<?php echo e($order->id); ?></td>
                    <td><?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></td>
                    <td><?php echo e(ucfirst($order->status)); ?></td>
                    <td>₱<?php echo e(number_format($order->total_amount, 2)); ?></td>
                    <td><?php echo e($order->created_at->format('M d, Y h:i A')); ?></td>
                    <td class="text-end"><a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-sm btn-outline-dark">View</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($orders->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/orders/index.blade.php ENDPATH**/ ?>