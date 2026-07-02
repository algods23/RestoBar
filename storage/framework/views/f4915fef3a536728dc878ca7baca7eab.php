<?php $__env->startSection('content'); ?>
<?php
    $showSales = in_array($reportType, ['sales', 'both'], true);
    $showInventory = in_array($reportType, ['inventory', 'both'], true);
    $activeButton = fn ($type) => $reportType === $type ? 'btn-dark' : 'btn-outline-dark';
    $activePeriod = fn ($value) => $period === $value ? 'btn-dark' : 'btn-outline-dark';
?>

<div class="d-flex flex-column flex-xl-row gap-3 justify-content-between align-items-xl-center mb-3">
    <div>
        <h1 class="h4 mb-1">Reports</h1>
        <div class="text-muted small"><?php echo e($from->format('M d, Y')); ?> to <?php echo e($to->format('M d, Y')); ?></div>
    </div>
    <a
        href="<?php echo e(route('reports.excel', ['type' => $reportType, 'period' => $period, 'from' => $from->toDateString(), 'to' => $to->toDateString()])); ?>"
        class="btn btn-success"
    >
        Export Excel
    </a>
</div>

<div class="card p-3 mb-4">
    <form action="<?php echo e(route('reports.index')); ?>" method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="period" id="reportPeriod" value="<?php echo e($period); ?>">
        <input type="hidden" name="type" id="reportType" value="<?php echo e($reportType); ?>">

        <div class="col-12">
            <label class="form-label fw-semibold d-block">Date Range</label>
            <div class="btn-group flex-wrap" role="group" aria-label="Date range">
                <button type="submit" name="period" value="current" class="btn <?php echo e($activePeriod('current')); ?>">Current</button>
                <button type="submit" name="period" value="week" class="btn <?php echo e($activePeriod('week')); ?>">Week</button>
                <button type="submit" name="period" value="month" class="btn <?php echo e($activePeriod('month')); ?>">Month</button>
                <button type="button" id="customPeriodBtn" class="btn <?php echo e($activePeriod('custom')); ?>">Custom</button>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">From</label>
            <input type="date" name="from" class="form-control" value="<?php echo e($from->toDateString()); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">To</label>
            <input type="date" name="to" class="form-control" value="<?php echo e($to->toDateString()); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold d-block">Report</label>
            <div class="btn-group w-100" role="group" aria-label="Report type">
                <button type="submit" name="type" value="sales" class="btn <?php echo e($activeButton('sales')); ?>">Sales</button>
                <button type="submit" name="type" value="inventory" class="btn <?php echo e($activeButton('inventory')); ?>">Inventory</button>
                <button type="submit" name="type" value="both" class="btn <?php echo e($activeButton('both')); ?>">Both</button>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Generate</button>
        </div>
    </form>
</div>

<?php if($showSales): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card p-3"><div class="text-muted">Total Sales</div><div class="h4 mb-0">&#8369;<?php echo e(number_format($salesSummary['sales'], 2)); ?></div></div></div>
        <div class="col-md-4"><div class="card p-3"><div class="text-muted">Orders</div><div class="h4 mb-0"><?php echo e(number_format($salesSummary['orders'])); ?></div></div></div>
        <div class="col-md-4"><div class="card p-3"><div class="text-muted">Subtotal</div><div class="h4 mb-0">&#8369;<?php echo e(number_format($salesSummary['subtotal'], 2)); ?></div></div></div>
    </div>

    <div class="card p-3 mb-4">
        <h2 class="h5">Best Selling Items</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Product</th><th>Qty Sold</th><th>Sales</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bestSellingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item->product?->name); ?></td>
                            <td><?php echo e($item->total_quantity); ?></td>
                            <td>&#8369;<?php echo e(number_format($item->total_sales, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No sales found for this date range.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-3">
        <h2 class="h5">Sales Details</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr><th>Date/Time</th><th>Order</th><th>Cashier</th><th>Type</th><th class="text-end">Subtotal</th><th class="text-end">Discount</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-nowrap"><?php echo e($order->created_at->format('M d, Y h:i A')); ?></td>
                            <td>#<?php echo e($order->id); ?></td>
                            <td><?php echo e($order->user?->name ?? 'N/A'); ?></td>
                            <td><?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></td>
                            <td class="text-end">&#8369;<?php echo e(number_format($order->subtotal, 2)); ?></td>
                            <td class="text-end">&#8369;<?php echo e(number_format($order->discount_amount, 2)); ?></td>
                            <td class="text-end fw-semibold">&#8369;<?php echo e(number_format($order->total_amount, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No completed orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if($showInventory): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Logs</div><div class="h4 mb-0"><?php echo e(number_format($inventorySummary['logs'])); ?></div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Stock In</div><div class="h4 mb-0"><?php echo e(number_format($inventorySummary['stock_in'])); ?></div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Stock Out</div><div class="h4 mb-0"><?php echo e(number_format($inventorySummary['stock_out'])); ?></div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="text-muted">Adjustments</div><div class="h4 mb-0"><?php echo e(number_format($inventorySummary['adjustments'])); ?></div></div></div>
    </div>

    <div class="card p-3">
        <h2 class="h5">Inventory Details</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr><th>Date/Time</th><th>User</th><th>Product</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Previous</th><th class="text-end">New</th><th>Notes</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $inventoryLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-nowrap"><?php echo e($log->created_at->format('M d, Y h:i A')); ?></td>
                            <td><?php echo e($log->user?->name ?? 'N/A'); ?></td>
                            <td><?php echo e($log->product?->name ?? 'N/A'); ?></td>
                            <td><span class="badge text-bg-light border"><?php echo e(str_replace('_', ' ', $log->type)); ?></span></td>
                            <td class="text-end"><?php echo e($log->quantity); ?></td>
                            <td class="text-end"><?php echo e($log->previous_stock); ?></td>
                            <td class="text-end fw-semibold"><?php echo e($log->new_stock); ?></td>
                            <td><?php echo e($log->notes); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">No inventory activity found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (function () {
        const customBtn = document.getElementById('customPeriodBtn');
        const periodInput = document.getElementById('reportPeriod');

        if (customBtn && periodInput) {
            customBtn.addEventListener('click', function () {
                periodInput.value = 'custom';
                customBtn.closest('form').submit();
            });
        }
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/reports/index.blade.php ENDPATH**/ ?>