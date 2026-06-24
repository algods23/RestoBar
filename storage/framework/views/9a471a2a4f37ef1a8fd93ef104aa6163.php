

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Tables</h1>
    <a href="<?php echo e(route('tables.create')); ?>" class="btn btn-dark">Add Table</a>
</div>

<?php if(session('error')): ?>
    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="card p-3">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Table</th>
                <th>Status</th>
                <th>Current Order</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>T<?php echo e($table->number); ?></td>
                    <td>
                        <?php if($table->is_occupied): ?>
                            <span class="badge text-bg-danger">Occupied</span>
                        <?php else: ?>
                            <span class="badge text-bg-success">Available</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($table->current_order_id): ?>
                            <a href="<?php echo e(route('orders.show', $table->current_order_id)); ?>">#<?php echo e($table->current_order_id); ?></a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?php echo e(route('tables.edit', $table)); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="<?php echo e(route('tables.destroy', $table)); ?>" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger" <?php echo e($table->is_occupied ? 'disabled' : ''); ?>>Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No tables yet. Add your first table.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php echo e($tables->links('pagination.default')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views\tables\index.blade.php ENDPATH**/ ?>