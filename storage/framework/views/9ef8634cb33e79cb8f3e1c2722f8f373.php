

<?php $__env->startSection('content'); ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h1 class="h5">Stock Adjustment</h1>
            <form method="POST" action="<?php echo e(route('inventory.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-2">
                    <label class="form-label">Product</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Select a product...</option>
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> (Stock: <?php echo e($product->stock); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="stock_in">Stock In</option>
                        <option value="stock_out">Stock Out</option>
                        <option value="adjustment">Adjustment</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Quantity</label>
                    <input name="quantity" type="number" min="1" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Notes</label>
                    <input name="notes" class="form-control">
                </div>
                <button class="btn btn-dark w-100">Save</button>
            </form>
        </div>

        <div class="card p-3">
            <h2 class="h6">Low Stock Products</h2>
            <?php $__currentLoopData = $lowStockProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span><?php echo e($product->name); ?></span>
                    <strong><?php echo e($product->stock); ?></strong>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-3">
            <h2 class="h5">Inventory Logs</h2>
            <table class="table align-middle">
                <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Previous</th><th>New</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($log->product?->name); ?></td>
                            <td><?php echo e($log->type); ?></td>
                            <td><?php echo e($log->quantity); ?></td>
                            <td><?php echo e($log->previous_stock); ?></td>
                            <td><?php echo e($log->new_stock); ?></td>
                            <td><?php echo e($log->created_at->format('M d, Y h:i A')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <?php echo e($logs->links('pagination.default')); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/inventory/index.blade.php ENDPATH**/ ?>