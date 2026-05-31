<aside class="sidebar">
    <div class="logo text-white">
        <div style="display:flex;align-items:center;gap:.4rem;padding:.4rem .75rem;">
            <i class="bi bi-shop-window" style="font-size:1rem;color:#fff"></i>
            <div class="brand" style="font-size:0.92rem;font-weight:700;line-height:1;">RestoBar POS & Inventory</div>
        </div>
    </div>

    <nav class="nav">
        <a href="<?php echo e(route('dashboard')); ?>" class="d-flex align-items-center <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
            <span class="icon"><i class="bi bi-speedometer2"></i></span>
            <span class="label">Dashboard</span>
        </a>
        <a href="<?php echo e(route('pos.index')); ?>" class="d-flex align-items-center <?php echo e(request()->routeIs('pos.*') ? 'active' : ''); ?>">
            <span class="icon"><i class="bi bi-basket3"></i></span>
            <span class="label">POS</span>
        </a>
        <a href="<?php echo e(route('orders.index')); ?>" class="d-flex align-items-center <?php echo e(request()->routeIs('orders.*') ? 'active' : ''); ?>">
            <span class="icon"><i class="bi bi-receipt"></i></span>
            <span class="label">Orders</span>
        </a>
        <a href="<?php echo e(route('products.index')); ?>" class="d-flex align-items-center <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>">
            <span class="icon"><i class="bi bi-box-seam"></i></span>
            <span class="label">Products</span>
        </a>
        <a href="<?php echo e(url('/categories')); ?>" class="d-flex align-items-center <?php echo e(request()->routeIs('categories.*') ? 'active' : ''); ?>">
            <span class="icon"><i class="bi bi-tags"></i></span>
            <span class="label">Categories</span>
        </a>
        <a href="<?php echo e(route('inventory.index')); ?>" class="d-flex align-items-center <?php echo e(request()->routeIs('inventory.*') ? 'active' : ''); ?>">
            <span class="icon"><i class="bi bi-clipboard-data"></i></span>
            <span class="label">Inventory</span>
        </a>
        <a href="<?php echo e(route('reports.index')); ?>" class="d-flex align-items-center <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
            <span class="icon"><i class="bi bi-bar-chart-line"></i></span>
            <span class="label">Reports</span>
        </a>
        <a href="/users" class="d-flex align-items-center">
            <span class="icon"><i class="bi bi-people"></i></span>
            <span class="label">Users</span>
        </a>
        <a href="/settings" class="d-flex align-items-center">
            <span class="icon"><i class="bi bi-gear"></i></span>
            <span class="label">Settings</span>
        </a>
    </nav>
</aside>
<?php /**PATH C:\laragon\www\RestoBar\resources\views/includes/sidebar.blade.php ENDPATH**/ ?>