<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Staff Management</h1>
    <a href="<?php echo e(route('users.create')); ?>" class="btn btn-dark">Add Staff</a>
</div>

<div class="card p-3">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Position</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($user->name); ?></td>
                    <td><?php echo e($user->email); ?></td>
                    <td>
                        <span class="badge <?php echo e($user->role === 'admin' ? 'bg-primary' : 'bg-secondary'); ?>">
                            <?php echo e(ucfirst($user->role)); ?>

                        </span>
                    </td>
                    <td><?php echo e($user->position ?? '—'); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-outline-secondary">Update</a>
                        <form method="POST" action="<?php echo e(route('users.destroy', $user)); ?>" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($users->links('pagination.default')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/users/index.blade.php ENDPATH**/ ?>