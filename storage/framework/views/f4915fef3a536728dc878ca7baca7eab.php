

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Reports</h1>
    <a href="<?php echo e(route('reports.pdf')); ?>" class="btn btn-dark">Export PDF</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card p-3"><div class="text-muted">Daily Sales</div><div class="h4 mb-0">₱<?php echo e(number_format($daily['sales'], 2)); ?></div></div></div>
    <div class="col-md-4"><div class="card p-3"><div class="text-muted">Weekly Sales</div><div class="h4 mb-0">₱<?php echo e(number_format($weekly['sales'], 2)); ?></div></div></div>
    <div class="col-md-4"><div class="card p-3"><div class="text-muted">Monthly Sales</div><div class="h4 mb-0">₱<?php echo e(number_format($monthly['sales'], 2)); ?></div></div></div>
</div>

<div class="card p-3">
    <h2 class="h5">Best Selling Items</h2>
    <table class="table align-middle">
        <thead><tr><th>Product</th><th>Qty Sold</th><th>Sales</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $bestSellingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product?->name); ?></td>
                    <td><?php echo e($item->total_quantity); ?></td>
                    <td>₱<?php echo e(number_format($item->total_sales, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/reports/index.blade.php ENDPATH**/ ?>