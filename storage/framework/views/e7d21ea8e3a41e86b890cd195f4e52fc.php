<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h1>RestoBar <?php echo e(ucfirst($period)); ?> Report</h1>
    <p>Orders: <?php echo e($summary['orders']); ?> | Sales: ₱<?php echo e(number_format($summary['sales'], 2)); ?></p>
    <h2>Best Selling Items</h2>
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Sales</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $bestSellingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product?->name); ?></td>
                    <td><?php echo e($item->total_quantity); ?></td>
                    <td>₱<?php echo e(number_format($item->total_sales, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\laragon\www\RestoBar\resources\views\reports\pdf.blade.php ENDPATH**/ ?>