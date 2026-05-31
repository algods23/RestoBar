<header class="top-header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
        <button class="toggle-btn" data-toggle="sidebar" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>
        <button class="d-lg-none btn btn-outline-secondary btn-sm" data-toggle="mobile-sidebar" aria-label="Mobile menu"><i class="bi bi-list"></i></button>
        <h4 class="mb-0 ms-2">{{ $title ?? (isset($pageTitle) ? $pageTitle : (ucfirst(request()->segment(1) ?: 'Dashboard'))) }}</h4>
    </div>

    <div class="d-flex align-items-center gap-3 me-2">
        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-bell"></i></button>
        @auth
            <div class="d-flex align-items-center gap-2">
                <div class="text-muted small">{{ auth()->user()->name }} ({{ auth()->user()->role }})</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">Logout</button>
                </form>
            </div>
        @endauth
    </div>
</header>
