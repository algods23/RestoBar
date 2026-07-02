@extends('layouts.app')

@section('content')
@php
    $availabilityTabs = [
        '' => ['label' => 'All', 'class' => 'secondary'],
        'available' => ['label' => 'Good', 'class' => 'success'],
        'low' => ['label' => 'Low', 'class' => 'warning'],
        'out' => ['label' => 'No Stock', 'class' => 'danger'],
    ];
    $currentAvailability = request('availability', '');
@endphp

<div class="row g-3 align-items-start">
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h1 class="h5 mb-1">Stock Adjustment</h1>
                        <div class="small text-muted">Update product stock instantly.</div>
                    </div>
                    <span class="badge text-bg-light border">Inventory</span>
                </div>

                <form method="POST" action="{{ route('inventory.store') }}" id="stockAdjustmentForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product</label>
                        <select name="product_id" id="adjustProduct" class="form-select" required>
                            <option value="">Select a product...</option>
                            @foreach($adjustmentProducts as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->stock }} in stock)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select">
                                <option value="stock_in">Stock In</option>
                                <option value="stock_out">Stock Out</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Quantity</label>
                            <input name="quantity" type="number" min="1" class="form-control" required>
                        </div>
                    </div>

                    <div class="my-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <input name="notes" class="form-control" placeholder="Optional note">
                    </div>

                    <button class="btn btn-dark w-100">Save Adjustment</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h6 mb-0">Logs</h2>
                    <span class="small text-muted">{{ $logs->total() }} logs</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">New</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="small text-muted text-nowrap">{{ $log->created_at?->format('M d, Y h:i A') }}</td>
                                    <td class="text-truncate" style="max-width: 120px;">{{ $log->user?->name ?? 'N/A' }}</td>
                                    <td class="text-truncate" style="max-width: 140px;">{{ $log->product?->name }}</td>
                                    <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', $log->type) }}</span></td>
                                    <td class="text-end">{{ $log->quantity }}</td>
                                    <td class="text-end fw-semibold">{{ $log->new_stock }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No stock activity yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
                        <a
                            href="{{ $logs->previousPageUrl() ?: '#' }}"
                            class="btn btn-sm btn-outline-secondary {{ $logs->onFirstPage() ? 'disabled' : '' }}"
                            aria-label="Previous logs page"
                        >
                            Prev
                        </a>
                        <span class="small text-muted text-nowrap">
                            Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                        </span>
                        <a
                            href="{{ $logs->nextPageUrl() ?: '#' }}"
                            class="btn btn-sm btn-outline-secondary {{ $logs->hasMorePages() ? '' : 'disabled' }}"
                            aria-label="Next logs page"
                        >
                            Next
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between mb-3">
                    <div>
                        <h2 class="h5 mb-1">Product Availability</h2>
                        <div class="small text-muted">Search, filter, and adjust stock from one table.</div>
                    </div>

                    <ul class="nav nav-pills gap-2" role="tablist">
                        @foreach ($availabilityTabs as $value => $tab)
                            <li class="nav-item" role="presentation">
                                <a
                                    class="nav-link {{ $currentAvailability === $value ? 'active' : '' }}"
                                    href="{{ route('inventory.index', array_filter([
                                        'search' => request('search'),
                                        'category_id' => request('category_id'),
                                        'availability' => $value,
                                    ], fn ($item) => $item !== null && $item !== '')) }}"
                                >
                                    {{ $tab['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <form action="{{ route('inventory.index') }}" method="GET" id="productFilterForm" class="row g-2 mb-3 align-items-center">
                    <input type="hidden" name="availability" value="{{ request('availability') }}">
                    <div class="col-md-6">
                        <label class="visually-hidden" for="productSearch">Search product name</label>
                        <input
                            type="text"
                            name="search"
                            id="productSearch"
                            class="form-control form-control-sm"
                            placeholder="Search product name..."
                            value="{{ request('search') }}"
                            autocomplete="off"
                        >
                    </div>
                    <div class="col-md-4">
                        <label class="visually-hidden" for="categoryFilter">Category</label>
                        <select name="category_id" id="categoryFilter" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary w-100 text-nowrap" title="Clear filters">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Stock</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $isCritical = $product->stock <= 0;
                                    $isLow = ! $isCritical && $product->isLowStock();
                                    $statusLabel = $isCritical ? 'No Stock' : ($isLow ? 'Low' : 'Good');
                                    $statusClass = $isCritical ? 'danger' : ($isLow ? 'warning' : 'success');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        <div class="small text-muted">{{ $product->barcode ?: 'No barcode' }}</div>
                                    </td>
                                    <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>
                                    <td class="text-end">
                                        <div class="fw-semibold">{{ $product->stock }}</div>
                                        <div class="small text-muted">min {{ $product->reorder_level }}</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="{{ $product->name }} actions">
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary">Update</a>
                                            <button
                                                type="button"
                                                class="btn btn-outline-dark"
                                                data-adjust-product="{{ $product->id }}"
                                                data-adjust-name="{{ $product->name }}"
                                            >
                                                Adjust
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between mt-3">
                    <div class="small text-muted">
                        Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                    </div>
                    {{ $products->links('pagination.default') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const filterForm = document.getElementById('productFilterForm');
        const searchInput = document.getElementById('productSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const adjustProduct = document.getElementById('adjustProduct');
        const stockAdjustmentForm = document.getElementById('stockAdjustmentForm');
        let searchTimer;

        if (filterForm && searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    filterForm.submit();
                }, 450);
            });
        }

        if (filterForm && categoryFilter) {
            categoryFilter.addEventListener('change', function () {
                filterForm.submit();
            });
        }

        document.querySelectorAll('[data-adjust-product]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!adjustProduct) {
                    return;
                }

                adjustProduct.value = button.dataset.adjustProduct;
                stockAdjustmentForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                adjustProduct.focus();
            });
        });
    })();
</script>
@endpush
