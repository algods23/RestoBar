

<?php $__env->startSection('content'); ?>
<h1 class="h4 mb-3">Orders</h1>
<div class="card p-3">
    <form method="GET" action="<?php echo e(url()->current()); ?>" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">Search Customer</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name..." value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">From Date & Time</label>
            <input type="datetime-local" name="from" class="form-control form-control-sm" value="<?php echo e(request('from')); ?>">
        </div>
        <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">To Date & Time</label>
            <input type="datetime-local" name="to" class="form-control form-control-sm" value="<?php echo e(request('to')); ?>">
        </div>
        <div class="col-md-3 col-sm-12 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>
            <?php if(request()->filled('from') || request()->filled('to') || request()->filled('search')): ?>
                <a href="<?php echo e(url()->current()); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>

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
                    <td><?php echo e(ucfirst($order->status)); ?></td>
                    <td>₱<?php echo e(number_format($order->total_amount, 2)); ?></td>
                    <td><?php echo e($order->created_at->format('h:i A')); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-sm btn-outline-dark">View</a>
                        <?php if($order->status === 'pending'): ?>
                            <button class="btn btn-sm btn-success pay-btn ms-1" data-id="<?php echo e($order->id); ?>" data-total="<?php echo e($order->total_amount); ?>">Pay</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($orders->links('pagination.default')); ?>

</div>
<?php echo $__env->make('orders.partials.payment-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/orders/index.blade.php ENDPATH**/ ?>