<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RestoBar' }}</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --sidebar-bg: #1f2937;
            --card-radius: 12px;
            --app-surface: #f6f3ee;
            --app-text: #111827;
            --app-muted: #6b7280;
            --app-border: #d9dee7;
            --app-dark: #20262d;
            --app-success: #168a4f;
            --app-warning: #ffc107;
            --app-danger: #dc3545;
        }

        html,body,#app{height:100%;}
        body {
            margin: 0;
            background: var(--app-surface);
            color: var(--app-text);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 16px;
        }

        *, *::before, *::after { box-sizing: border-box; }

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

        .main-content { margin-left: var(--sidebar-width); flex:1; min-width: 0; transition:margin-left .3s ease; }

        .top-header { height:60px; background:#fff; display:flex; align-items:center; padding:0 .75rem; border-bottom:1px solid #e6e6e6; }
        .top-header h4 { font-size:1rem; margin:0; }
        .top-header .toggle-btn { background:transparent; border:0; font-size:1.25rem; }

        .card { border:0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); border-radius: var(--card-radius); }

        main.container-fluid { width: 100%; padding: 1.5rem; }

        /* Local UI fallback keeps the desktop app usable if packaged CSS fails to load. */
        .row { display: flex; flex-wrap: wrap; margin-right: -.75rem; margin-left: -.75rem; }
        .row > * { padding-right: .75rem; padding-left: .75rem; max-width: 100%; }
        .g-1 { row-gap: .25rem; }
        .g-2 { row-gap: .5rem; }
        .g-3 { row-gap: 1rem; }
        .gap-1 { gap: .25rem; }
        .gap-2 { gap: .5rem; }
        .gap-3 { gap: 1rem; }
        .d-flex { display: flex !important; }
        .d-none { display: none !important; }
        .flex-wrap { flex-wrap: wrap !important; }
        .flex-grow-1 { flex-grow: 1 !important; }
        .align-items-center { align-items: center !important; }
        .align-items-end { align-items: flex-end !important; }
        .justify-content-between { justify-content: space-between !important; }
        .text-start { text-align: left !important; }
        .text-center { text-align: center !important; }
        .text-muted { color: var(--app-muted) !important; }
        .text-dark { color: var(--app-text) !important; }
        .text-success { color: #087443 !important; }
        .fw-semibold { font-weight: 600 !important; }
        .fw-bold { font-weight: 700 !important; }
        .small { font-size: .875rem !important; }
        .h5 { font-size: 1.25rem; }
        .h6 { font-size: 1rem; }
        .mb-0 { margin-bottom: 0 !important; }
        .mb-1 { margin-bottom: .25rem !important; }
        .mb-2 { margin-bottom: .5rem !important; }
        .mb-3 { margin-bottom: 1rem !important; }
        .mt-1 { margin-top: .25rem !important; }
        .mt-2 { margin-top: .5rem !important; }
        .mt-3 { margin-top: 1rem !important; }
        .ms-2 { margin-left: .5rem !important; }
        .me-2 { margin-right: .5rem !important; }
        .m-1 { margin: .25rem !important; }
        .p-2 { padding: .5rem !important; }
        .p-3 { padding: 1rem !important; }
        .px-1 { padding-left: .25rem !important; padding-right: .25rem !important; }
        .px-2 { padding-left: .5rem !important; padding-right: .5rem !important; }
        .px-3 { padding-left: 1rem !important; padding-right: 1rem !important; }
        .py-0 { padding-top: 0 !important; padding-bottom: 0 !important; }
        .py-2 { padding-top: .5rem !important; padding-bottom: .5rem !important; }
        .py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
        .pt-1 { padding-top: .25rem !important; }
        .pt-3 { padding-top: 1rem !important; }
        .pb-2 { padding-bottom: .5rem !important; }
        .border { border: 1px solid var(--app-border) !important; }
        .border-top { border-top: 1px solid var(--app-border) !important; }
        .rounded { border-radius: 8px !important; }
        .bg-light { background: #f8fafc !important; }
        .w-100 { width: 100% !important; }
        .h-100 { height: 100% !important; }
        .position-relative { position: relative !important; }
        .position-absolute { position: absolute !important; }
        .position-fixed { position: fixed !important; }
        .sticky-top { position: sticky; z-index: 1020; }
        .top-0 { top: 0 !important; }
        .end-0 { right: 0 !important; }
        .bottom-0 { bottom: 0 !important; }
        .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .card {
            background: #fff;
            border: 0;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        }

        .form-control, .form-select {
            display: block;
            width: 100%;
            min-height: 38px;
            padding: .45rem .75rem;
            color: var(--app-text);
            background-color: #fff;
            border: 1px solid var(--app-border);
            border-radius: 6px;
            font: inherit;
        }

        .form-control-lg, .btn-lg {
            min-height: 58px;
            font-size: 1.35rem;
            border-radius: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            min-height: 32px;
            padding: .35rem .7rem;
            border: 1px solid transparent;
            border-radius: 6px;
            background: #fff;
            color: var(--app-text);
            font: inherit;
            line-height: 1.2;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:disabled, .btn.disabled { opacity: .65; cursor: not-allowed; }
        .btn-sm { min-height: 30px; padding: .25rem .6rem; font-size: .875rem; }
        .btn-xs { min-height: 24px; padding: .1rem .35rem; font-size: .75rem; }
        .btn-dark { color: #fff; background: var(--app-dark); border-color: var(--app-dark); }
        .btn-light { background: #f8fafc; border-color: var(--app-border); }
        .btn-success { color: #fff; background: var(--app-success); border-color: var(--app-success); }
        .btn-warning { color: #111827; background: var(--app-warning); border-color: var(--app-warning); }
        .btn-danger { color: #fff; background: var(--app-danger); border-color: var(--app-danger); }
        .btn-outline-dark { color: var(--app-dark); border-color: var(--app-dark); }
        .btn-outline-secondary { color: #4b5563; border-color: #9ca3af; }
        .btn-outline-danger { color: var(--app-danger); border-color: var(--app-danger); }
        .btn-outline-danger:hover, .btn-outline-danger.active { color: #fff; background: var(--app-danger); }

        .badge {
            display: inline-block;
            padding: .35em .55em;
            border-radius: 999px;
            font-size: .75em;
            font-weight: 700;
        }

        .bg-danger { background: var(--app-danger) !important; color: #fff !important; }
        .bg-warning { background: var(--app-warning) !important; }

        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: .45rem; border-bottom: 1px solid #edf0f5; text-align: left; }
        .table-responsive { max-width: 100%; overflow-x: auto; }

        #productGrid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
        #productGrid > .product-card-wrapper { width: 100%; padding: 0; }
        .add-product-card {
            min-height: 315px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            padding: .9rem;
            background: #f8fafc;
        }
        .add-product-card img { width: 100%; border-radius: 8px; object-fit: cover; }

        @media (min-width: 992px) {
            .col-lg-8 { flex: 0 0 auto; width: 66.66666667%; }
            .col-lg-4 { flex: 0 0 auto; width: 33.33333333%; }
            .d-xl-block { display: block !important; }
        }

        @media (min-width: 768px) {
            .col-md-2 { flex: 0 0 auto; width: 16.66666667%; }
            .col-md-4 { flex: 0 0 auto; width: 33.33333333%; }
        }

        @media (max-width: 1199px) {
            #productGrid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        /* Responsive behaviour */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .mobile-sidebar-open .sidebar { transform: translateX(0); }
            .main-content { margin-left:0; }
            #productGrid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .row > .col-lg-8, .row > .col-lg-4 { width: 100%; }
        }

        @media (max-width: 575px) {
            main.container-fluid { padding: 1rem; }
            #productGrid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="app" class="app-wrapper">
    @include('includes.sidebar')

    <div class="main-content">
        @include('includes.header')

        <main class="container-fluid py-4">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
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
@stack('scripts')
</body>
</html>
