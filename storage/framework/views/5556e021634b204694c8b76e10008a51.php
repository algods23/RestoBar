<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?php echo e($method === 'POST' ? 'Add Staff' : 'Edit Staff'); ?></h1>
    <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card p-3">
    <form action="<?php echo e($action); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php if($method === 'PUT'): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo e($user->name ?? old('name')); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo e($user->email ?? old('email')); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password <?php echo e($method === 'PUT' ? '(leave blank to keep current)' : ''); ?></label>
            <input type="password" name="password" class="form-control" <?php echo e($method === 'POST' ? 'required' : ''); ?>>
        </div>

        <?php if($method === 'POST'): ?>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="cashier" <?php echo e(($user->role ?? old('role')) === 'cashier' ? 'selected' : ''); ?>>Cashier</option>
                <option value="admin" <?php echo e(($user->role ?? old('role')) === 'admin' ? 'selected' : ''); ?>>Admin</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Position</label>
            <input type="text" name="position" class="form-control" value="<?php echo e($user->position ?? old('position')); ?>" placeholder="e.g., Manager, Waiter, Chef">
        </div>

        <button type="submit" class="btn btn-primary"><?php echo e($method === 'POST' ? 'Create Staff' : 'Update Staff'); ?></button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/users/form.blade.php ENDPATH**/ ?>