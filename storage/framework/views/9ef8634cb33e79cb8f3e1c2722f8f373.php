<?php $__env->startSection('content'); ?>
<?php
    $availabilityTabs = [
        '' => ['label' => 'All', 'class' => 'secondary'],
        'available' => ['label' => 'Good', 'class' => 'success'],
        'low' => ['label' => 'Low', 'class' => 'warning'],
        'out' => ['label' => 'No Stock', 'class' => 'danger'],
    ];
    $currentAvailability = request('availability', '');
?>

<div class="row g-3 align-items-start">
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h1 class="h5 mb-1">Stock Adjustment</h1>
                        <div class="small text-muted">Update product stock instantly.</div>
                    </div>
                    <span class="badge text-bg-light border">Inventory</span>
                </div>

                <form method="POST" action="<?php echo e(route('inventory.store')); ?>" id="stockAdjustmentForm">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product</label>
                        <select name="product_id" id="adjustProduct" class="form-select" required>
                            <option value="">Select a product...</option>
                            <?php $__currentLoopData = $adjustmentProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> (<?php echo e($product->stock); ?> in stock)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select">
                                <option value="stock_in">Stock In</option>
                                <option value="stock_out">Stock Out</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Quantity</label>
                            <input name="quantity" type="number" min="1" class="form-control" required>
                        </div>
                    </div>

                    <div class="my-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <input name="notes" class="form-control" placeholder="Optional note">
                    </div>

                    <button class="btn btn-dark w-100">Save Adjustment</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h6 mb-0">Logs</h2>
                    <span class="small text-muted"><?php echo e($logs->total()); ?> logs</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">New</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-truncate" style="max-width: 140px;"><?php echo e($log->product?->name); ?></td>
                                    <td><span class="badge text-bg-light border"><?php echo e(str_replace('_', ' ', $log->type)); ?></span></td>
                                    <td class="text-end"><?php echo e($log->quantity); ?></td>
                                    <td class="text-end fw-semibold"><?php echo e($log->new_stock); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No stock activity yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($logs->hasPages()): ?>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
                        <a
                            href="<?php echo e($logs->previousPageUrl() ?: '#'); ?>"
                            class="btn btn-sm btn-outline-secondary <?php echo e($logs->onFirstPage() ? 'disabled' : ''); ?>"
                            aria-label="Previous logs page"
                        >
                            Prev
                        </a>
                        <span class="small text-muted text-nowrap">
                            Page <?php echo e($logs->currentPage()); ?> of <?php echo e($logs->lastPage()); ?>

                        </span>
                        <a
                            href="<?php echo e($logs->nextPageUrl() ?: '#'); ?>"
                            class="btn btn-sm btn-outline-secondary <?php echo e($logs->hasMorePages() ? '' : 'disabled'); ?>"
                            aria-label="Next logs page"
                        >
                            Next
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between mb-3">
                    <div>
                        <h2 class="h5 mb-1">Product Availability</h2>
                        <div class="small text-muted">Search, filter, and adjust stock from one table.</div>
                    </div>

                    <ul class="nav nav-pills gap-2" role="tablist">
                        <?php $__currentLoopData = $availabilityTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="nav-item" role="presentation">
                                <a
                                    class="nav-link <?php echo e($currentAvailability === $value ? 'active' : ''); ?>"
                                    href="<?php echo e(route('inventory.index', array_filter([
                                        'search' => request('search'),
                                        'category_id' => request('category_id'),
                                        'availability' => $value,
                                    ], fn ($item) => $item !== null && $item !== ''))); ?>"
                                >
                                    <?php echo e($tab['label']); ?>

                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <form action="<?php echo e(route('inventory.index')); ?>" method="GET" id="productFilterForm" class="row g-2 mb-3 align-items-center">
                    <input type="hidden" name="availability" value="<?php echo e(request('availability')); ?>">
                    <div class="col-md-6">
                        <label class="visually-hidden" for="productSearch">Search product name</label>
                        <input
                            type="text"
                            name="search"
                            id="productSearch"
                            class="form-control form-control-sm"
                            placeholder="Search product name..."
                            value="<?php echo e(request('search')); ?>"
                            autocomplete="off"
                        >
                    </div>
                    <div class="col-md-4">
                        <label class="visually-hidden" for="categoryFilter">Category</label>
                        <select name="category_id" id="categoryFilter" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if(request('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-sm btn-outline-secondary w-100 text-nowrap" title="Clear filters">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Stock</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $isCritical = $product->stock <= 0;
                                    $isLow = ! $isCritical && $product->isLowStock();
                                    $statusLabel = $isCritical ? 'No Stock' : ($isLow ? 'Low' : 'Good');
                                    $statusClass = $isCritical ? 'danger' : ($isLow ? 'warning' : 'success');
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($product->name); ?></div>
                                        <div class="small text-muted"><?php echo e($product->barcode ?: 'No barcode'); ?></div>
                                    </td>
                                    <td><?php echo e($product->category?->name ?? 'Uncategorized'); ?></td>
                                    <td class="text-end">
                                        <div class="fw-semibold"><?php echo e($product->stock); ?></div>
                                        <div class="small text-muted">min <?php echo e($product->reorder_level); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-<?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="<?php echo e($product->name); ?> actions">
                                            <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-outline-secondary">Edit</a>
                                            <button
                                                type="button"
                                                class="btn btn-outline-dark"
                                                data-adjust-product="<?php echo e($product->id); ?>"
                                                data-adjust-name="<?php echo e($product->name); ?>"
                                            >
                                                Adjust
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between mt-3">
                    <div class="small text-muted">
                        Showing <?php echo e($products->firstItem() ?? 0); ?> to <?php echo e($products->lastItem() ?? 0); ?> of <?php echo e($products->total()); ?> products
                    </div>
                    <?php echo e($products->links('pagination.default')); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (function () {
        const filterForm = document.getElementById('productFilterForm');
        const searchInput = document.getElementById('productSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const adjustProduct = document.getElementById('adjustProduct');
        const stockAdjustmentForm = document.getElementById('stockAdjustmentForm');
        let searchTimer;

        if (filterForm && searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    filterForm.submit();
                }, 450);
            });
        }

        if (filterForm && categoryFilter) {
            categoryFilter.addEventListener('change', function () {
                filterForm.submit();
            });
        }

        document.querySelectorAll('[data-adjust-product]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!adjustProduct) {
                    return;
                }

                adjustProduct.value = button.dataset.adjustProduct;
                stockAdjustmentForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                adjustProduct.focus();
            });
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/inventory/index.blade.php ENDPATH**/ ?>