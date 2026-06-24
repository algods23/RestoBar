

<?php $__env->startSection('content'); ?>
<div class="card p-4 mx-auto" style="max-width: 760px;">
    <h1 class="h4 mb-3"><?php echo e($table->exists ? 'Edit Table' : 'Add Table'); ?></h1>
    <form method="POST" action="<?php echo e($action); ?>">
        <?php echo csrf_field(); ?>
        <?php if($method !== 'POST'): ?> <?php echo method_field($method); ?> <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Table Number</label>
            <input type="number" min="1" name="number" class="form-control" value="<?php echo e(old('number', $table->number)); ?>" required>
            <div class="form-text">This is the number shown on POS as T1, T2, and so on.</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-dark">Save</button>
            <a href="<?php echo e(route('tables.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views\tables\form.blade.php ENDPATH**/ ?>