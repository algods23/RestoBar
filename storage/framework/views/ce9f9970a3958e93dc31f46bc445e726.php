<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - RestoBar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">RestoBar Login</h1>
                    <form method="POST" action="<?php echo e(route('login.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo e(old('email')); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button class="btn btn-dark w-100">Login</button>
                    </form>
                    <div class="text-muted small mt-3">
                        Demo accounts: admin@restobar.test or cashier@restobar.test, password: password
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\laragon\www\RestoBar\resources\views/auth/login.blade.php ENDPATH**/ ?>