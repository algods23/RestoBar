

<?php $__env->startSection('content'); ?>
<div class="card p-4 mx-auto" style="max-width: 900px;">
    <h1 class="h4 mb-3"><?php echo e($product->exists ? 'Edit Product' : 'Add Product'); ?></h1>
    <form method="POST" action="<?php echo e($action); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php if($method !== 'POST'): ?> <?php echo method_field($method); ?> <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if(old('category_id', $product->category_id) == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="<?php echo e(old('name', $product->name)); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Barcode</label>
                <input name="barcode" class="form-control" value="<?php echo e(old('barcode', $product->barcode)); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Image</label>
                <input name="image" type="file" accept="image/*" class="form-control">
                <div class="form-text">Upload a product photo for POS cards.</div>
                <?php if($product->image): ?>
                    <img src="<?php echo e($product->imageUrl()); ?>" alt="<?php echo e($product->name); ?>" class="mt-2 rounded border" style="width: 140px; height: 100px; object-fit: cover;">
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">Price</label>
                <input name="price" type="number" step="0.01" class="form-control" value="<?php echo e(old('price', $product->price)); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Stock</label>
                <input name="stock" type="number" min="0" class="form-control" value="<?php echo e(old('stock', $product->stock)); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Reorder Level</label>
                <input name="reorder_level" type="number" min="0" class="form-control" value="<?php echo e(old('reorder_level', $product->reorder_level)); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?php if(old('status', $product->status) === 'available'): echo 'selected'; endif; ?>>Available</option>
                    <option value="out_of_stock" <?php if(old('status', $product->status) === 'out_of_stock'): echo 'selected'; endif; ?>>Out of Stock</option>
                    <option value="inactive" <?php if(old('status', $product->status) === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control"><?php echo e(old('description', $product->description)); ?></textarea>
            </div>
        </div>
        <button class="btn btn-dark mt-3">Save</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/products/form.blade.php ENDPATH**/ ?>