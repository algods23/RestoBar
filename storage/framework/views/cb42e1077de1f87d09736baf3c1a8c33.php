<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'RestoBar'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --sidebar-bg: #1f2937;
            --card-radius: 12px;
        }

        html,body,#app{height:100%;}
        body { background: #f6f3ee; }

        .app-wrapper { display:flex; min-height:100vh; }

        .sidebar {
            position:fixed; left:0; top:0; bottom:0;
            width:var(--sidebar-width); background:var(--sidebar-bg); color:#fff;
            transition: width .3s ease, transform .3s ease;
            z-index:1030; overflow:hidden;
        }

        .sidebar .logo { padding:0.75rem 1rem; font-weight:700; font-size:0.95rem; display:flex; align-items:center; gap:.5rem; }
        .sidebar .logo .brand { white-space:nowrap; font-size:0.95rem; line-height:1.1; }
        .sidebar .logo .brand { font-size:1.02rem; }
        .sidebar .nav { padding: .4rem 0; display:flex; flex-direction:column; }
        .sidebar .nav a { color: #d1d5db; display:flex; gap:.6rem; align-items:center; padding:.5rem 1rem; text-decoration:none; white-space:nowrap; font-size:0.95rem; }
        .sidebar .nav a .label { display:inline-block; overflow:hidden; text-overflow:ellipsis; }
        .sidebar .nav a .icon { font-size:1.05rem; width:28px; }
        .sidebar .nav a:hover { background:rgba(255,255,255,0.03); color:#fff; }
        .sidebar .nav a .icon { font-size:1.2rem; width:26px; text-align:center; }
        .sidebar .nav a.active { background:#111827; color:#fff; }

        .sidebar-collapsed .sidebar { width:var(--sidebar-collapsed-width); }
        .sidebar-collapsed .sidebar .nav a span.label { display:none; }
        .sidebar-collapsed .sidebar .logo .brand { display:none; }
        .sidebar-collapsed .main-content { margin-left: var(--sidebar-collapsed-width); }

        .main-content { margin-left: var(--sidebar-width); flex:1; transition:margin-left .3s ease; }

        .top-header { height:60px; background:#fff; display:flex; align-items:center; padding:0 .75rem; border-bottom:1px solid #e6e6e6; }
        .top-header h4 { font-size:1rem; margin:0; }
        .top-header .toggle-btn { background:transparent; border:0; font-size:1.25rem; }

        .card { border:0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); border-radius: var(--card-radius); }

        /* Responsive behaviour */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .mobile-sidebar-open .sidebar { transform: translateX(0); }
            .main-content { margin-left:0; }
        }
    </style>
</head>
<body>
<div id="app" class="app-wrapper">
    <?php echo $__env->make('includes.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <?php echo $__env->make('includes.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <main class="container-fluid py-4">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function(){
        const body = document.body;
        const toggle = () => body.classList.toggle('sidebar-collapsed');
        const mobileToggle = () => body.classList.toggle('mobile-sidebar-open');

        document.addEventListener('click', (e) => {
            const target = e.target;
            if (target.closest('[data-toggle="sidebar"]')) toggle();
            if (target.closest('[data-toggle="mobile-sidebar"]')) mobileToggle();
        });
    })();
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\RestoBar\resources\views/layouts/app.blade.php ENDPATH**/ ?>