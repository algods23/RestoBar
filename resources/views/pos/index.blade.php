@extends('layouts.app')

@section('content')
<div class="row g-3">

    {{-- LEFT: Products --}}
    <div class="col-lg-8">

        {{-- Search Bar --}}
        <div class="card p-3 mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input id="barcodeInput" class="form-control form-control-lg" placeholder="Barcode">
                </div>
                <div class="col-md-4">
                    <input id="searchInput" class="form-control form-control-lg" placeholder="Search product...">
                </div>
                <div class="col-md-2">
                    <button id="searchBtn" class="btn btn-dark btn-lg w-100">Search</button>
                </div>
                <div class="col-md-3">
                    <button id="resetBtn" class="btn btn-outline-secondary btn-lg w-100">Show All</button>
                </div>
            </div>
        </div>

        {{-- Category Filter --}}
        <div class="card px-3 pt-3 pb-2 mb-3">
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-dark category-filter active" data-category="all">All</button>
                @foreach($categories as $category)
                    <button class="btn btn-sm btn-outline-dark category-filter" data-category="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Products</h2>
                <span class="text-muted small">Click a card to add to cart</span>
            </div>
            <div id="productGrid" class="row g-3">
                @foreach($products as $product)
                    <div class="col-md-4 col-xl-3 product-card-wrapper" data-category="{{ $product->category_id }}">
                        <button class="btn btn-light border w-100 text-start add-product-card h-100 position-relative"
                            data-id="{{ $product->id }}"
                            data-stock="{{ $product->stock }}"
                            {{ $product->stock <= 0 ? 'disabled' : '' }}>
                            @if($product->stock <= 0)
                                <span class="badge bg-danger position-absolute top-0 end-0 m-1">Out of Stock</span>
                            @elseif($product->stock <= 5)
                                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-1">Low Stock</span>
                            @endif
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                class="w-100 rounded mb-2" style="height: 140px; object-fit: cover;">
                            <div class="fw-semibold text-dark text-truncate">{{ $product->name }}</div>
                            <div class="small text-muted text-truncate">{{ $product->category?->name }}</div>
                            <div class="small text-muted">Stock: {{ $product->stock }}</div>
                            <div class="fw-bold text-success">₱{{ number_format($product->price, 2) }}</div>
                        </button>
                    </div>
                @endforeach
            </div>
            <div id="searchResults" class="mt-3 row g-2"></div>
        </div>
    </div>

    {{-- RIGHT: Cart --}}
    <div class="col-lg-4">
        <div class="card p-3 sticky-top" style="top: 88px;">
            <h2 class="h5 mb-3">Current Cart</h2>

            {{-- Customer Name --}}
            <div class="mb-2">
                <label class="form-label fw-semibold">Customer Name</label>
                <input type="text" id="customerName" class="form-control" placeholder="Enter customer name">
            </div>

            {{-- Table Selection --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Table(s)</label>
                <div class="d-flex flex-wrap gap-2" id="tableGrid">
                    @foreach($tables as $table)
                        <button type="button"
                            class="btn btn-sm table-btn {{ $table->is_occupied ? 'btn-danger disabled' : 'btn-outline-secondary' }}"
                            data-table="{{ $table->number }}"
                            {{ $table->is_occupied ? 'disabled' : '' }}>
                            T{{ $table->number }}
                        </button>
                    @endforeach
                </div>
                <div class="form-text small">Selected: <span id="selectedTablesDisplay">None</span></div>
            </div>

            {{-- Cart Items --}}
            <div id="cartItems">
                @include('pos.partials.cart-items', ['cart' => $cart])
            </div>

            {{-- Checkout Form --}}
            <form id="checkoutForm" class="mt-3">
                @csrf

                <div class="mb-2">
                    <label class="form-label">Order Type</label>
                    <select name="order_type" class="form-select">
                        <option value="dine_in">Dine-in</option>
                        <option value="takeout">Takeout</option>
                        <option value="delivery">Delivery</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Discount (₱)</label>
                    <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control" value="0" min="0">
                </div>

                <div class="mb-2">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" id="paymentMethodSelect" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                {{-- Amount Paid (cash only) --}}
                <div class="mb-2" id="amountPaidRow">
                    <label class="form-label">Amount Paid (₱)</label>
                    <input type="number" step="0.01" name="amount_paid" id="amountPaidInput" class="form-control" min="0" placeholder="0.00">
                    <div class="form-text">Change: <strong class="text-success">₱<span id="changeDisplay">0.00</span></strong></div>
                </div>

                {{-- Reference (non-cash) --}}
                <div class="mb-2" id="referenceRow" style="display:none">
                    <label class="form-label">Reference No.</label>
                    <input type="text" name="payment_reference" id="paymentReference" class="form-control" placeholder="Transaction reference">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="vat_enabled" id="vatEnabled" value="1" checked>
                    <label class="form-check-label" for="vatEnabled">Apply 12% VAT</label>
                </div>

                <button type="button" id="checkoutBtn" class="btn btn-success w-100 btn-lg">
                    Checkout
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Checkout Confirmation Modal --}}
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-2">
                    <tr><td class="text-muted">Customer</td><td id="modal_customer" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">Table(s)</td><td id="modal_tables" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">Order Type</td><td id="modal_type" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">Payment</td><td id="modal_payment" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">Subtotal</td><td id="modal_subtotal" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">Discount</td><td id="modal_discount" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">VAT</td><td id="modal_vat" class="fw-semibold">—</td></tr>
                    <tr class="table-success"><td><strong>Total</strong></td><td id="modal_total" class="fw-bold fs-5">—</td></tr>
                </table>
                <div id="modal_change_row" class="alert alert-info py-2 mb-0" style="display:none">
                    Amount Paid: <strong>₱<span id="modal_paid">0.00</span></strong> &nbsp;|&nbsp;
                    Change: <strong>₱<span id="modal_change">0.00</span></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmCheckoutBtn" class="btn btn-success px-4">Confirm & Place Order</button>
            </div>
        </div>
    </div>
</div>

{{-- Stock Warning Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="stockToast" class="toast align-items-center text-bg-warning border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="stockToastMsg">Stock warning</div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const cartItemsEl = document.getElementById('cartItems');
const productGrid = document.getElementById('productGrid');
const searchResults = document.getElementById('searchResults');

// ── Helpers ──────────────────────────────────────────────────────────────────

function money(v) {
    return '₱' + Number(v).toFixed(2);
}

function showToast(msg) {
    document.getElementById('stockToastMsg').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(document.getElementById('stockToast')).show();
}

async function postJson(url, data, method = 'POST') {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    });
    if (!res.ok) throw await res.json();
    return res.json();
}

// ── Tables ────────────────────────────────────────────────────────────────────

let selectedTables = [];

document.getElementById('tableGrid').addEventListener('click', e => {
    const btn = e.target.closest('.table-btn');
    if (!btn || btn.disabled) return;

    const num = btn.dataset.table;
    if (selectedTables.includes(num)) {
        selectedTables = selectedTables.filter(t => t !== num);
        btn.classList.replace('btn-dark', 'btn-outline-secondary');
    } else {
        selectedTables.push(num);
        btn.classList.replace('btn-outline-secondary', 'btn-dark');
    }

    document.getElementById('selectedTablesDisplay').textContent =
        selectedTables.length ? selectedTables.map(t => 'T' + t).join(', ') : 'None';
});

// ── Category Filter ───────────────────────────────────────────────────────────

document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.category-filter').forEach(b => {
            b.classList.remove('btn-dark', 'active');
            b.classList.add('btn-outline-dark');
        });
        btn.classList.add('btn-dark', 'active');
        btn.classList.remove('btn-outline-dark');

        const cat = btn.dataset.category;
        document.querySelectorAll('.product-card-wrapper').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category == cat) ? '' : 'none';
        });
    });
});

// ── Cart Rendering ────────────────────────────────────────────────────────────

function renderCart(payload) {
    window.lastCartPayload = payload;
    const { items, totals } = payload;

    if (!items || !items.length) {
        cartItemsEl.innerHTML = '<div class="text-muted small py-2">Cart is empty.</div>';
        updateTotals();
        return;
    }

    cartItemsEl.innerHTML = `
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Item</th><th>Qty</th><th>Price</th><th></th></tr>
                </thead>
                <tbody>
                    ${items.map(item => `
                        <tr>
                            <td class="small">${item.name}</td>
                            <td style="width:90px">
                                <input class="form-control form-control-sm qty-input"
                                    data-id="${item.product_id}"
                                    data-stock="${item.stock ?? 9999}"
                                    type="number" min="1"
                                    value="${item.quantity}">
                            </td>
                            <td class="small">${money(item.price)}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger remove-item"
                                    data-id="${item.product_id}">×</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div id="cartTotals" class="border-top pt-2 small mt-2">
            <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="cart_subtotal">${money(totals.subtotal)}</strong></div>
            <div class="d-flex justify-content-between"><span>Discount</span><strong id="cart_discount">${money(totals.discount_amount)}</strong></div>
            <div class="d-flex justify-content-between"><span>VAT</span><strong id="cart_vat">${money(totals.vat_amount)}</strong></div>
            <div class="d-flex justify-content-between fs-6 mt-1"><span><strong>Total</strong></span><strong id="cart_total">${money(totals.total)}</strong></div>
        </div>`;

    updateTotals();
}

// ── Totals ────────────────────────────────────────────────────────────────────

function getCalcTotals() {
    const payload = window.lastCartPayload || { totals: { subtotal: 0 } };
    const subtotal = Number(payload.totals?.subtotal || 0);
    const discount = Math.max(0, Number(document.getElementById('discountInput')?.value || 0));
    const vatEnabled = document.getElementById('vatEnabled')?.checked ?? true;
    const vat = vatEnabled ? Math.round(Math.max(0, subtotal - discount) * 0.12 * 100) / 100 : 0;
    const total = Math.max(0, Math.round((subtotal - discount + vat) * 100) / 100);
    return { subtotal, discount, vat, total };
}

function updateTotals() {
    const calc = getCalcTotals();
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = money(val); };
    set('cart_subtotal', calc.subtotal);
    set('cart_discount', calc.discount);
    set('cart_vat', calc.vat);
    set('cart_total', calc.total);
    updateChange();
}

function updateChange() {
    const calc = getCalcTotals();
    const paid = Number(document.getElementById('amountPaidInput')?.value || 0);
    const change = Math.max(0, Math.round((paid - calc.total) * 100) / 100);
    const el = document.getElementById('changeDisplay');
    if (el) el.textContent = change.toFixed(2);
}

document.getElementById('discountInput').addEventListener('input', updateTotals);
document.getElementById('vatEnabled').addEventListener('change', updateTotals);
document.getElementById('amountPaidInput').addEventListener('input', updateChange);

// ── Payment Method ────────────────────────────────────────────────────────────

document.getElementById('paymentMethodSelect').addEventListener('change', function () {
    const isCash = this.value === 'cash';
    document.getElementById('amountPaidRow').style.display = isCash ? '' : 'none';
    document.getElementById('referenceRow').style.display = !isCash ? '' : 'none';
    const refInput = document.getElementById('paymentReference');
    isCash ? refInput.removeAttribute('required') : refInput.setAttribute('required', 'required');
});

// ── Search ────────────────────────────────────────────────────────────────────

function productCard(p) {
    const fallback = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400"><rect width="600" height="400" fill="#f1f5f9"/><text x="300" y="210" text-anchor="middle" font-family="Arial" font-size="28" fill="#94a3b8">No Image</text></svg>');
    const outOfStock = p.stock <= 0;
    const lowStock = p.stock > 0 && p.stock <= 5;
    return `
        <div class="col-md-4 col-xl-3">
            <button class="btn btn-light border w-100 text-start add-product-card h-100 position-relative"
                data-id="${p.id}" data-stock="${p.stock}" ${outOfStock ? 'disabled' : ''}>
                ${outOfStock ? '<span class="badge bg-danger position-absolute top-0 end-0 m-1">Out of Stock</span>' : ''}
                ${lowStock ? '<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-1">Low Stock</span>' : ''}
                <img src="${p.image_url || fallback}" alt="${p.name}" class="w-100 rounded mb-2" style="height:140px;object-fit:cover">
                <div class="fw-semibold text-dark text-truncate">${p.name}</div>
                <div class="small text-muted text-truncate">${p.category?.name ?? ''}</div>
                <div class="small text-muted">Stock: ${p.stock}</div>
                <div class="fw-bold text-success">${money(p.price)}</div>
            </button>
        </div>`;
}

async function searchProducts() {
    const query = document.getElementById('barcodeInput').value || document.getElementById('searchInput').value;
    const res = await fetch(`{{ route('pos.search') }}?query=${encodeURIComponent(query)}`, {
        headers: { 'Accept': 'application/json' }
    });
    const products = await res.json();
    searchResults.innerHTML = products.length
        ? products.map(productCard).join('')
        : '<div class="text-muted">No products found.</div>';
    productGrid.style.display = 'none';
    bindSearchResultButtons();
}

document.getElementById('searchBtn').addEventListener('click', searchProducts);
document.getElementById('resetBtn').addEventListener('click', () => {
    document.getElementById('barcodeInput').value = '';
    document.getElementById('searchInput').value = '';
    searchResults.innerHTML = '';
    productGrid.style.display = '';
});
document.getElementById('barcodeInput').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); searchProducts(); }});
document.getElementById('searchInput').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); searchProducts(); }});

// ── Add to Cart ───────────────────────────────────────────────────────────────

async function addToCart(productId, stock) {
    // check current qty in cart vs stock
    const existingInput = cartItemsEl.querySelector(`.qty-input[data-id="${productId}"]`);
    const currentQty = existingInput ? Number(existingInput.value) : 0;
    if (currentQty >= stock) {
        showToast(`Only ${stock} in stock — cannot add more.`);
        return;
    }
    const payload = await postJson(`{{ route('pos.cart.add') }}`, { product_id: productId, quantity: 1 });
    renderCart(payload);
}

productGrid.addEventListener('click', async e => {
    const btn = e.target.closest('.add-product-card');
    if (!btn) return;
    await addToCart(btn.dataset.id, Number(btn.dataset.stock));
});

function bindSearchResultButtons() {
    searchResults.querySelectorAll('.add-product-card').forEach(btn => {
        btn.addEventListener('click', async () => {
            await addToCart(btn.dataset.id, Number(btn.dataset.stock));
        });
    });
}

// ── Cart Events ───────────────────────────────────────────────────────────────

cartItemsEl.addEventListener('change', async e => {
    if (!e.target.classList.contains('qty-input')) return;
    const stock = Number(e.target.dataset.stock);
    const qty = Number(e.target.value);
    if (qty > stock) {
        showToast(`Only ${stock} available in stock.`);
        e.target.value = stock;
        return;
    }
    const payload = await postJson(`{{ route('pos.cart.update') }}`, {
        product_id: e.target.dataset.id,
        quantity: qty
    }, 'PATCH');
    renderCart(payload);
});

cartItemsEl.addEventListener('click', async e => {
    if (!e.target.classList.contains('remove-item')) return;
    e.preventDefault();
    const payload = await postJson(`{{ route('pos.cart.remove') }}`, {
        product_id: e.target.dataset.id
    }, 'DELETE');
    renderCart(payload);
});

// ── Checkout Modal ────────────────────────────────────────────────────────────

document.getElementById('checkoutBtn').addEventListener('click', () => {
    const calc = getCalcTotals();
    const method = document.getElementById('paymentMethodSelect').value;
    const paid = Number(document.getElementById('amountPaidInput')?.value || 0);
    const change = Math.max(0, Math.round((paid - calc.total) * 100) / 100);
    const orderType = document.querySelector('select[name="order_type"]').value;

    // Populate modal
    document.getElementById('modal_customer').textContent =
        document.getElementById('customerName').value || '—';
    document.getElementById('modal_tables').textContent =
        selectedTables.length ? selectedTables.map(t => 'T' + t).join(', ') : '—';
    document.getElementById('modal_type').textContent =
        orderType.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
    document.getElementById('modal_payment').textContent =
        method.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
    document.getElementById('modal_subtotal').textContent = money(calc.subtotal);
    document.getElementById('modal_discount').textContent = money(calc.discount);
    document.getElementById('modal_vat').textContent = money(calc.vat);
    document.getElementById('modal_total').textContent = money(calc.total);

    const changeRow = document.getElementById('modal_change_row');
    if (method === 'cash') {
        changeRow.style.display = '';
        document.getElementById('modal_paid').textContent = paid.toFixed(2);
        document.getElementById('modal_change').textContent = change.toFixed(2);
    } else {
        changeRow.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('checkoutModal')).show();
});

document.getElementById('confirmCheckoutBtn').addEventListener('click', async () => {
    const formData = new FormData(document.getElementById('checkoutForm'));
    const payload = Object.fromEntries(formData.entries());
    payload.vat_enabled = formData.get('vat_enabled') ? 1 : 0;
    payload.customer_name = document.getElementById('customerName').value;
    payload.tables = selectedTables;

    const res = await fetch(`{{ route('pos.checkout') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'text/html,application/xhtml+xml'
        },
        body: JSON.stringify(payload)
    });

    bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();

    if (res.redirected) {
        window.location.href = res.url;
    } else {
        window.location.reload();
    }
});
</script>
@endpush