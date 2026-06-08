

<?php $__env->startSection('content'); ?>
<div class="card p-4 mx-auto" style="max-width: 760px;">
    <h1 class="h4 mb-3"><?php echo e($category->exists ? 'Edit Category' : 'Add Category'); ?></h1>
    <form method="POST" action="<?php echo e($action); ?>">
        <?php echo csrf_field(); ?>
        <?php if($method !== 'POST'): ?> <?php echo method_field($method); ?> <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="<?php echo e(old('name', $category->name)); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?php echo e(old('description', $category->description)); ?></textarea>
        </div>
        <button class="btn btn-dark">Save</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/categories/form.blade.php ENDPATH**/ ?>