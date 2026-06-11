

<?php $__env->startSection('content'); ?>
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-1">Order #<?php echo e($order->id); ?></h1>
            <div class="text-muted"><?php echo e($order->created_at->format('M d, Y h:i A')); ?></div>
        </div>
        <div>
            <a href="<?php echo e(route('orders.receipt', $order)); ?>" class="btn btn-outline-dark">Print Receipt</a>
            <?php if($order->status === 'pending'): ?>
                <button class="btn btn-success pay-btn" data-id="<?php echo e($order->id); ?>" data-total="<?php echo e($order->total_amount); ?>">Pay</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><strong>Cashier:</strong> <?php echo e($order->user?->name); ?></div>
        <div class="col-md-4"><strong>Type:</strong> <?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></div>
        <div class="col-md-4"><strong>Status:</strong> <?php echo e(ucfirst($order->status)); ?></div>
    </div>

    <table class="table">
        <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Price</th><th>Subtotal</th><?php if($order->status === 'pending'): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product?->name); ?></td>
                    <td><?php echo e($item->item_type === 'dine_in' ? '🍽 Dine-in' : '🥡 Takeout'); ?></td>
                    <td><?php echo e($item->quantity); ?></td>
                    <td>₱<?php echo e(number_format($item->price, 2)); ?></td>
                    <td>₱<?php echo e(number_format($item->subtotal, 2)); ?></td>
                    <?php if($order->status === 'pending'): ?>
                    <td class="text-end">
                        <form action="<?php echo e(route('orders.items.remove', [$order, $item])); ?>" method="POST" class="d-inline" onsubmit="return confirm('Remove this item? Stock will be restored.');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php if($order->status === 'pending'): ?>
    <div class="card bg-light p-3 mb-4 border-0">
        <h6 class="mb-2">Add Item to Order</h6>
        <form action="<?php echo e(route('orders.items.add', $order)); ?>" method="POST" class="row g-2 align-items-end">
            <?php echo csrf_field(); ?>
            <div class="col-md-5">
                <label class="form-label small">Product</label>
                <select name="product_id" class="form-select form-select-sm" required>
                    <option value="">Select a product...</option>
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> (₱<?php echo e($product->price); ?> | Stock: <?php echo e($product->stock); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Type</label>
                <select name="item_type" class="form-select form-select-sm" required>
                    <option value="dine_in">Dine-in</option>
                    <option value="takeout">Takeout</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Quantity</label>
                <input type="number" name="quantity" class="form-control form-control-sm" min="1" value="1" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-dark w-100">Add Item</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="ms-auto" style="max-width: 320px;">
        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>₱<?php echo e(number_format($order->subtotal, 2)); ?></strong></div>
        <div class="d-flex justify-content-between"><span>Discount</span><strong>₱<?php echo e(number_format($order->discount_amount, 2)); ?></strong></div>
        <div class="d-flex justify-content-between"><span>VAT</span><strong>₱<?php echo e(number_format($order->vat_amount, 2)); ?></strong></div>
        <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>₱<?php echo e(number_format($order->total_amount, 2)); ?></strong></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('click', event => {
    if (!event.target.classList.contains('pay-btn')) return;
    const id = event.target.dataset.id;
    const total = Number(event.target.dataset.total) || 0;
    // reuse the modal logic from index: create if missing and show
    const showPaymentModal = window.showPaymentModal;
    if (typeof showPaymentModal === 'function') {
        showPaymentModal(id, total);
    } else {
        // fallback: redirect to orders index where modal exists
        window.location.href = '/orders';
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/orders/show.blade.php ENDPATH**/ ?>