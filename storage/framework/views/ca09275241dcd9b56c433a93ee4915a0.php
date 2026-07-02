<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1">Transactions</h1>
        <div class="text-muted"><?php echo e($date->format('F d, Y')); ?></div>
    </div>
    <form method="GET" action="<?php echo e(route('transactions.index')); ?>" class="d-flex gap-2 align-items-end">
        <div>
            <label class="form-label small text-muted mb-1">Date</label>
            <input type="date" name="date" class="form-control form-control-sm" value="<?php echo e($date->toDateString()); ?>">
        </div>
        <button class="btn btn-sm btn-dark">View</button>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 h-100">
            <div class="text-muted small">Total Sales</div>
            <div class="h4 mb-0">&#8369;<?php echo e(number_format($salesTotal, 2)); ?></div>
        </div>
    </div>
    <?php $__currentLoopData = ['cash' => 'Cash', 'card' => 'Card', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-3 col-sm-6">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?php echo e($label); ?> Received</div>
                <div class="h5 mb-0">&#8369;<?php echo e(number_format($paymentTotals->get($method, 0), 2)); ?></div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 mb-0 fw-bold">Payments Received</h2>
        <span class="text-muted small"><?php echo e($transactions->count()); ?> transaction<?php echo e($transactions->count() === 1 ? '' : 's'); ?></span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Order</th>
                    <th>Cashier</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-end">Amount Received</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($payment->created_at->format('h:i A')); ?></td>
                        <td>
                            <?php if($payment->order): ?>
                                <a href="<?php echo e(route('orders.show', $payment->order)); ?>" class="text-decoration-none">#<?php echo e($payment->order_id); ?></a>
                            <?php else: ?>
                                #<?php echo e($payment->order_id); ?>

                            <?php endif; ?>
                        </td>
                        <td><?php echo e($payment->user?->name ?? '—'); ?></td>
                        <td><?php echo e(str_replace('_', ' ', ucfirst($payment->method))); ?></td>
                        <td><?php echo e($payment->reference ?: '—'); ?></td>
                        <td class="text-end fw-semibold">&#8369;<?php echo e(number_format($payment->amount, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-muted text-center py-4">No transactions recorded for this date.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/transactions/index.blade.php ENDPATH**/ ?>