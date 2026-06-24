

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
                        <?php $__currentLoopData = $adjustmentProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> (Stock: <?php echo e($product->stock); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="stock_in">Stock In</option>
                        <option value="stock_out">Stock Out</option>
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

        <div class="card p-3 mb-3">
            <h2 class="h6">Low Stock Products</h2>
            <?php $__empty_1 = true; $__currentLoopData = $lowStockProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span><?php echo e($product->name); ?></span>
                    <strong><?php echo e($product->stock); ?></strong>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-muted py-2">No low stock products.</div>
            <?php endif; ?>
        </div>

        <div class="card p-3">
            <h2 class="h6">Inventory Logs</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>New</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($log->product?->name); ?></td>
                                <td><?php echo e($log->type); ?></td>
                                <td><?php echo e($log->quantity); ?></td>
                                <td><?php echo e($log->new_stock); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($logs->links('pagination.default')); ?>

        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-3">
            <h2 class="h5">Product Availability</h2>
            <form action="<?php echo e(route('inventory.index')); ?>" method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search product name..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php if(request('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="availability" class="form-select">
                        <option value="">All Status</option>
                        <option value="available" <?php if(request('availability') === 'available'): echo 'selected'; endif; ?>>Available</option>
                        <option value="low" <?php if(request('availability') === 'low'): echo 'selected'; endif; ?>>Low Stock</option>
                        <option value="out" <?php if(request('availability') === 'out'): echo 'selected'; endif; ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-fill">Filter</button>
                    <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-end">Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($product->name); ?></td>
                                <td><?php echo e($product->category?->name ?? 'Uncategorized'); ?></td>
                                <td class="text-end">
                                    <strong><?php echo e($product->stock); ?></strong>
                                    <div class="small text-muted">Reorder: <?php echo e($product->reorder_level); ?></div>
                                </td>
                                <td>
                                    <?php if($product->stock <= 0): ?>
                                        <span class="badge bg-danger">Out</span>
                                    <?php elseif($product->isLowStock()): ?>
                                        <span class="badge bg-warning text-dark">Low</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($products->links('pagination.default')); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views\inventory\index.blade.php ENDPATH**/ ?>