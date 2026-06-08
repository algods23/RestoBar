

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Products</h1>
    <a href="<?php echo e(route('products.create')); ?>" class="btn btn-dark">Add Product</a>
</div>

<div class="card p-3 mb-3">
    <form action="<?php echo e(route('products.index')); ?>" method="GET" class="row g-2">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search by name..." value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-md-5">
            <select name="category_id" class="form-select">
                <option value="">All Categories</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if(request('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100">Filter</button>
        </div>
    </form>
</div>

<div class="card p-3">
    <table class="table align-middle">
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($product->isLowStock() ? 'table-warning' : ''); ?>">
                    <td><img src="<?php echo e($product->imageUrl()); ?>" alt="<?php echo e($product->name); ?>" style="width:56px;height:40px;object-fit:cover;border-radius:8px;"></td>
                    <td><?php echo e($product->name); ?></td>
                    <td><?php echo e($product->category?->name); ?></td>
                    <td>₱<?php echo e(number_format($product->price, 2)); ?></td>
                    <td><?php echo e($product->stock); ?></td>
                    <td><?php echo e(ucfirst($product->status)); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="<?php echo e(route('products.destroy', $product)); ?>" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($products->links('pagination.default')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/products/index.blade.php ENDPATH**/ ?>