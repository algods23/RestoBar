

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Categories</h1>
    <a href="<?php echo e(route('categories.create')); ?>" class="btn btn-dark">Add Category</a>
</div>

<div class="card p-3">
    <table class="table align-middle">
        <thead><tr><th>Name</th><th>Description</th><th>Products</th><th></th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($category->name); ?></td>
                    <td><?php echo e($category->description); ?></td>
                    <td><?php echo e($category->products_count); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('categories.edit', $category)); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="<?php echo e(route('categories.destroy', $category)); ?>" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($categories->links('pagination.default')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/categories/index.blade.php ENDPATH**/ ?>