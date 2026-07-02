<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h2>RestoBar <?php echo e(ucfirst($reportType)); ?> Report</h2>
    <p><?php echo e($from->format('M d, Y')); ?> to <?php echo e($to->format('M d, Y')); ?></p>

    <?php if($reportType === 'sales'): ?>
        <table border="1">
            <tr><th>Total Sales</th><td><?php echo e(number_format($salesSummary['sales'], 2, '.', '')); ?></td></tr>
            <tr><th>Orders</th><td><?php echo e($salesSummary['orders']); ?></td></tr>
            <tr><th>Subtotal</th><td><?php echo e(number_format($salesSummary['subtotal'], 2, '.', '')); ?></td></tr>
        </table>

        <h3>Sales Details</h3>
        <table border="1">
            <tr><th>Date/Time</th><th>Order</th><th>Cashier</th><th>Type</th><th>Subtotal</th><th>Discount</th><th>Total</th></tr>
            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($order->created_at->format('Y-m-d H:i')); ?></td>
                    <td>#<?php echo e($order->id); ?></td>
                    <td><?php echo e($order->user?->name ?? 'N/A'); ?></td>
                    <td><?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></td>
                    <td><?php echo e(number_format($order->subtotal, 2, '.', '')); ?></td>
                    <td><?php echo e(number_format($order->discount_amount, 2, '.', '')); ?></td>
                    <td><?php echo e(number_format($order->total_amount, 2, '.', '')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>

        <h3>Best Selling Items</h3>
        <table border="1">
            <tr><th>Product</th><th>Qty Sold</th><th>Sales</th></tr>
            <?php $__currentLoopData = $bestSellingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product?->name); ?></td>
                    <td><?php echo e($item->total_quantity); ?></td>
                    <td><?php echo e(number_format($item->total_sales, 2, '.', '')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    <?php else: ?>
        <table border="1">
            <tr><th>Logs</th><td><?php echo e($inventorySummary['logs']); ?></td></tr>
            <tr><th>Stock In</th><td><?php echo e($inventorySummary['stock_in']); ?></td></tr>
            <tr><th>Stock Out</th><td><?php echo e($inventorySummary['stock_out']); ?></td></tr>
            <tr><th>Adjustments</th><td><?php echo e($inventorySummary['adjustments']); ?></td></tr>
        </table>

        <h3>Inventory Details</h3>
        <table border="1">
            <tr><th>Date/Time</th><th>User</th><th>Product</th><th>Type</th><th>Qty</th><th>Previous</th><th>New</th><th>Notes</th></tr>
            <?php $__currentLoopData = $inventoryLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($log->created_at->format('Y-m-d H:i')); ?></td>
                    <td><?php echo e($log->user?->name ?? 'N/A'); ?></td>
                    <td><?php echo e($log->product?->name ?? 'N/A'); ?></td>
                    <td><?php echo e(str_replace('_', ' ', $log->type)); ?></td>
                    <td><?php echo e($log->quantity); ?></td>
                    <td><?php echo e($log->previous_stock); ?></td>
                    <td><?php echo e($log->new_stock); ?></td>
                    <td><?php echo e($log->notes); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\laragon\www\RestoBar\resources\views/reports/excel.blade.php ENDPATH**/ ?>