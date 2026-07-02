

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <div class="col"><div class="card p-3"><div class="text-muted">Sales Today</div><div class="h3 mb-0">₱<?php echo e(number_format($totalSalesToday, 2)); ?></div></div></div>
    <div class="col"><div class="card p-3"><div class="text-muted">Orders Today</div><div class="h3 mb-0"><?php echo e($ordersToday); ?></div></div></div>
    <div class="col"><div class="card p-3"><div class="text-muted">This Week</div><div class="h3 mb-0">₱<?php echo e(number_format($totalSalesThisWeek, 2)); ?></div></div></div>
    <div class="col"><div class="card p-3"><div class="text-muted">This Month</div><div class="h3 mb-0">₱<?php echo e(number_format($totalSalesThisMonth, 2)); ?></div></div></div>
    <div class="col"><div class="card p-3"><div class="text-muted">Low Stock Items</div><div class="h3 mb-0"><?php echo e($lowStockProducts); ?></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card p-3 h-100">
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-sm-center mb-3">
                <h2 class="h5 mb-0">Sales Overview</h2>
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?php echo e(route('dashboard', ['period' => 'day'])); ?>" class="btn <?php echo e(request('period') === 'day' || !request('period') ? 'btn-dark' : 'btn-outline-dark'); ?>">Day</a>
                    <a href="<?php echo e(route('dashboard', ['period' => 'week'])); ?>" class="btn <?php echo e(request('period') === 'week' ? 'btn-dark' : 'btn-outline-dark'); ?>">Week</a>
                    <a href="<?php echo e(route('dashboard', ['period' => 'month'])); ?>" class="btn <?php echo e(request('period') === 'month' ? 'btn-dark' : 'btn-outline-dark'); ?>">Month</a>
                    <a href="<?php echo e(route('dashboard', ['period' => 'year'])); ?>" class="btn <?php echo e(request('period') === 'year' ? 'btn-dark' : 'btn-outline-dark'); ?>">Year</a>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-sm-6">
                    <input type="date" id="dateFrom" class="form-control form-control-sm" value="<?php echo e(request('from') ?? ''); ?>" placeholder="From">
                </div>
                <div class="col-sm-6">
                    <input type="date" id="dateTo" class="form-control form-control-sm" value="<?php echo e(request('to') ?? ''); ?>" placeholder="To">
                </div>
            </div>
            <canvas id="salesChart" height="110"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-3 h-100">
            <h2 class="h5">Best Selling Items</h2>
            <div class="list-group list-group-flush">
                <?php $__currentLoopData = $bestSellingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-group-item d-flex justify-content-between">
                        <span><?php echo e($item->product?->name); ?></span>
                        <strong><?php echo e($item->total_quantity); ?></strong>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($salesChart)); ?>,
        datasets: [{
            label: 'Sales',
            data: <?php echo json_encode(array_values($salesChart)); ?>,
            backgroundColor: '#111827',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Handle date range inputs
const dateFrom = document.getElementById('dateFrom');
const dateTo = document.getElementById('dateTo');
const periodButtons = document.querySelectorAll('.btn-group a');

function updateChart() {
    const from = dateFrom.value;
    const to = dateTo.value;
    const url = new URL(window.location.href);
    
    if (from) url.searchParams.set('from', from);
    if (to) url.searchParams.set('to', to);
    
    window.location.href = url.toString();
}

if (dateFrom) {
    dateFrom.addEventListener('change', function() {
        if (this.value && dateTo.value) {
            updateChart();
        }
    });
}

if (dateTo) {
    dateTo.addEventListener('change', function() {
        if (this.value && dateFrom.value) {
            updateChart();
        }
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/dashboard.blade.php ENDPATH**/ ?>