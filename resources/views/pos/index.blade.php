@extends('layouts.app')
@section('content')
<div class="row g-3">
    {{-- LEFT: Products --}}
    <div class="col-lg-8">
        {{-- Search Bar --}}
        <div class="card p-3 mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input id="searchInput" class="form-control form-control-lg" placeholder="Search product...">
                </div>
                <div class="col-md-2">
                    <button id="searchBtn" class="btn btn-dark btn-lg w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <button id="resetBtn" class="btn btn-outline-secondary btn-lg w-100">Reset</button>
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
        <div class="card sticky-top" style="top: 88px; padding: 10px 12px;">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <h2 class="h6 mb-0 fw-bold">Current Cart</h2>
                <button type="button" id="clearCartBtn" class="btn btn-outline-danger btn-sm py-0 px-2" title="Clear all items" style="font-size: 12px;">
                    🗑 Clear
                </button>
            </div>

            {{-- Customer Name --}}
            <div class="mb-1">
                <input type="text" id="customerName" class="form-control form-control-sm" placeholder="Customer name (optional)">
            </div>

            {{-- Table Selection --}}
            <div class="mb-1">
                <div class="d-flex flex-wrap gap-1" id="tableGrid">
                    @foreach($tables as $table)
                        <button type="button"
                            class="btn btn-xs table-btn py-0 px-1 {{ $table->is_occupied ? 'btn-danger disabled' : 'btn-outline-secondary' }}"
                            data-table="{{ $table->number }}"
                            style="font-size: 11px; line-height: 1.6;"
                            {{ $table->is_occupied ? 'disabled' : '' }}>
                            T{{ $table->number }}
                        </button>
                    @endforeach
                </div>
                <div class="text-muted" style="font-size: 11px;">Selected: <span id="selectedTablesDisplay">None</span></div>
            </div>

            {{-- Order Setup --}}
            <form id="checkoutForm" class="border rounded p-2 mb-2 bg-light">
                @csrf
                <label class="form-label mb-1 small fw-semibold">Order Type</label>
                <select name="order_type" id="orderTypeSelect" class="form-select form-select-sm">
                    <option value="dine_in">Dine-in</option>
                    <option value="takeout">Takeout</option>
                    <option value="delivery">Delivery</option>
                    <option value="mixed">Mixed</option>
                </select>
      
            </form>

            {{-- Cart Items (auto-height, no scroll) --}}
            <div id="cartItems">
                @include('pos.partials.cart-items', ['cart' => $cart])
            </div>

            {{-- Cart Totals --}}
            <div id="cartTotals" class="border-top pt-1 mt-1" style="font-size: 12px;">
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="cart_subtotal">₱{{ number_format($totals['subtotal'], 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>Discount</span><strong id="cart_discount">₱{{ number_format($totals['discount_amount'], 2) }}</strong></div>
                <div class="d-flex justify-content-between mt-1"><span><strong>Total</strong></span><strong id="cart_total" style="font-size: 14px;">₱{{ number_format($totals['total'], 2) }}</strong></div>
            </div>

            <div class="mt-2 d-flex gap-2">
                <button type="button" id="checkoutBtn" class="btn btn-success flex-grow-1">
                    Checkout
                </button>
                <button type="button" id="addToOrderBtn" class="btn btn-warning flex-grow-1" title="Add items to an existing table order">
                    ➕ Add to Order
                </button>
            </div>
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
                    <tr><td class="text-muted">Subtotal</td><td id="modal_subtotal" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">Discount</td><td id="modal_discount" class="fw-semibold">—</td></tr>
                    <tr><td class="text-muted">VAT</td><td id="modal_vat" class="fw-semibold">—</td></tr>
                    <tr class="table-success"><td><strong>Total</strong></td><td id="modal_total" class="fw-bold fs-5">—</td></tr>
                </table>

                {{-- Mixed order breakdown --}}
                <div id="modal_type_breakdown" class="mb-3" style="display:none">
                    <div class="small fw-semibold text-muted mb-1">Items by Type:</div>
                    <div id="modal_dine_in_items" class="small text-muted"></div>
                    <div id="modal_takeout_items" class="small text-muted mt-1"></div>
                </div>

                <hr>
                <div class="payment-details-section">
                    <h6 class="fw-bold mb-3">Payment Details</h6>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Discount (₱)</label>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="paymentMethodSelect" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmCheckoutBtn" class="btn btn-success px-4">Place Order</button>
            </div>
        </div>
    </div>
</div>

{{-- Add to Existing Order Modal --}}
<div class="modal fade" id="addToOrderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-25">
                <h5 class="modal-title">➕ Add to Existing Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Select the table to add the current cart items to their existing order:</p>
                <div id="occupiedTablesList" class="mb-3">
                    <div class="text-center text-muted py-3">Loading tables...</div>
                </div>
                {{-- Selected table info --}}
                <div id="addToOrderDetails" class="border rounded p-3 bg-light" style="display:none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong id="addOrder_table" class="fs-5"></strong>
                            <span class="text-muted ms-2" id="addOrder_customer"></span>
                        </div>
                        <span class="badge bg-primary" id="addOrder_orderId"></span>
                    </div>
                    <div class="small text-muted mb-2">
                        Current total: <strong id="addOrder_currentTotal"></strong>
                        &nbsp;|&nbsp; Items: <strong id="addOrder_itemsCount"></strong>
                    </div>
                    <hr class="my-2">
                    <div class="small fw-semibold mb-1">Items to add (from cart):</div>
                    <div id="addOrder_cartSummary" class="small"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmAddToOrderBtn" class="btn btn-warning px-4" disabled>
                    Add Items to Order
                </button>
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
const productGrid  = document.getElementById('productGrid');
const searchResults = document.getElementById('searchResults');

// Seed lastCartPayload from server-rendered cart so checkout modal works on fresh load
window.lastCartPayload = @json(['items' => array_values($cart), 'totals' => $totals]);

// ── Helpers ──────────────────────────────────────────────────────────────────
function money(v) { return '₱' + Number(v).toFixed(2); }

function showToast(msg) {
    document.getElementById('stockToastMsg').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(document.getElementById('stockToast')).show();
}

function orderTypeLabel(type) {
    return {
        dine_in: 'Dine-in',
        takeout: 'Takeout',
        delivery: 'Delivery',
        mixed: 'Mixed',
    }[type] || 'Dine-in';
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

// ── Auto-set Order Type from cart items ──────────────────────────────────────
function autoSetOrderType() {
    const items = window.lastCartPayload?.items ?? [];
    const types = new Set(items.map(i => i.item_type ?? 'dine_in'));
    const sel = document.getElementById('orderTypeSelect');
    if (types.size === 0) return;
    if (types.size > 1) {
        sel.value = 'mixed';
    } else if (types.has('delivery')) {
        sel.value = 'delivery';
    } else if (types.has('takeout')) {
        sel.value = 'takeout';
    } else {
        sel.value = 'dine_in';
    }
}

async function setAllCartItemTypes(newType) {
    if (newType === 'mixed') return;

    const items = [...(window.lastCartPayload?.items ?? [])];
    for (const item of items) {
        const oldType = item.item_type ?? 'dine_in';
        if (oldType === newType) continue;

        const payload = await postJson(`{{ route('pos.cart.update') }}`, {
            product_id: item.product_id,
            item_type: oldType,
            new_item_type: newType,
            quantity: item.quantity
        }, 'PATCH');
        window.lastCartPayload = payload;
    }

    if (window.lastCartPayload) {
        renderCart(window.lastCartPayload);
    }
}

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
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    ${items.map(item => `
                        <tr>
                            <td class="small">${item.name}</td>
                            <td style="width:80px">
                                <input class="form-control form-control-sm qty-input"
                                    data-id="${item.product_id}"
                                    data-type="${item.item_type ?? 'dine_in'}"
                                    data-stock="${item.stock ?? 9999}"
                                    type="number" min="1"
                                    value="${item.quantity}">
                            </td>
                            <td class="small">${money(item.price)}</td>
                            <td style="width:100px">
                                <select class="form-select form-select-sm item-type-select"
                                    data-id="${item.product_id}"
                                    data-type="${item.item_type ?? 'dine_in'}">
                                    <option value="dine_in" ${(item.item_type ?? 'dine_in') === 'dine_in' ? 'selected' : ''}>Dine-in</option>
                                    <option value="takeout" ${(item.item_type ?? 'dine_in') === 'takeout' ? 'selected' : ''}>Takeout</option>
                                    <option value="delivery" ${(item.item_type ?? 'dine_in') === 'delivery' ? 'selected' : ''}>Delivery</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger remove-item"
                                    data-id="${item.product_id}"
                                    data-type="${item.item_type ?? 'dine_in'}">×</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>`;

    autoSetOrderType();
    updateTotals();
}

// ── Totals ────────────────────────────────────────────────────────────────────
function getCalcTotals() {
    const payload  = window.lastCartPayload || { totals: { subtotal: 0 } };
    const subtotal = Number(payload.totals?.subtotal || 0);
    const discount = Math.max(0, Number(document.getElementById('discountInput')?.value || 0));
    const vatEnabled = document.getElementById('vatEnabled')?.checked ?? true;
    const vat  = vatEnabled ? Math.round(Math.max(0, subtotal - discount) * 0.12 * 100) / 100 : 0;
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
    const calc  = getCalcTotals();
    const paid  = Number(document.getElementById('amountPaidInput')?.value || 0);
    const change = Math.max(0, Math.round((paid - calc.total) * 100) / 100);
    const el = document.getElementById('changeDisplay');
    if (el) el.textContent = change.toFixed(2);
}

const discountInputEl = document.getElementById('discountInput');
const vatEnabledEl    = document.getElementById('vatEnabled');

function refreshModalSummary() {
    const calc = getCalcTotals();
    const el = (id) => document.getElementById(id);
    if (el('modal_discount')) el('modal_discount').textContent = money(calc.discount);
    if (el('modal_subtotal')) el('modal_subtotal').textContent = money(calc.subtotal);
    if (el('modal_vat'))      el('modal_vat').textContent      = money(calc.vat);
    if (el('modal_total'))    el('modal_total').textContent    = money(calc.total);
    // Recompute change display
    const paid   = Number(el('amountPaidInput')?.value || 0);
    const change = Math.max(0, Math.round((paid - calc.total) * 100) / 100);
    if (el('changeDisplay')) el('changeDisplay').textContent = change.toFixed(2);
    updateConfirmButtonText();
}

if (discountInputEl) discountInputEl.addEventListener('input', () => { updateTotals(); refreshModalSummary(); });
if (vatEnabledEl)    vatEnabledEl.addEventListener('change',   () => { updateTotals(); refreshModalSummary(); });

// ── Search ────────────────────────────────────────────────────────────────────
function productCard(p) {
    const fallback = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400"><rect width="600" height="400" fill="#f1f5f9"/><text x="300" y="210" text-anchor="middle" font-family="Arial" font-size="28" fill="#94a3b8">No Image</text></svg>'
    );
    const outOfStock = p.stock <= 0;
    const lowStock   = p.stock > 0 && p.stock <= 5;
    return `
        <div class="col-md-4 col-xl-3">
            <button class="btn btn-light border w-100 text-start add-product-card h-100 position-relative"
                data-id="${p.id}" data-stock="${p.stock}" ${outOfStock ? 'disabled' : ''}>
                ${outOfStock ? '<span class="badge bg-danger position-absolute top-0 end-0 m-1">Out of Stock</span>' : ''}
                ${lowStock   ? '<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-1">Low Stock</span>' : ''}
                <img src="${p.image_url || fallback}" alt="${p.name}" class="w-100 rounded mb-2" style="height:140px;object-fit:cover">
                <div class="fw-semibold text-dark text-truncate">${p.name}</div>
                <div class="small text-muted text-truncate">${p.category?.name ?? ''}</div>
                <div class="small text-muted">Stock: ${p.stock}</div>
                <div class="fw-bold text-success">${money(p.price)}</div>
            </button>
        </div>`;
}

async function searchProducts() {
    const barcodeEl = document.getElementById('barcodeInput');
    const searchEl = document.getElementById('searchInput');
    const query = (barcodeEl?.value || searchEl?.value || '');
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

const searchBtn = document.getElementById('searchBtn');
if (searchBtn) searchBtn.addEventListener('click', searchProducts);

const resetBtn = document.getElementById('resetBtn');
if (resetBtn) {
    resetBtn.addEventListener('click', () => {
        const barcodeEl = document.getElementById('barcodeInput');
        const searchEl = document.getElementById('searchInput');
        if (barcodeEl) barcodeEl.value = '';
        if (searchEl) searchEl.value = '';
        searchResults.innerHTML = '';
        productGrid.style.display = '';
    });
}

const barcodeEl = document.getElementById('barcodeInput');
if (barcodeEl) {
    barcodeEl.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); searchProducts(); }});
}

const searchEl = document.getElementById('searchInput');
if (searchEl) {
    searchEl.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); searchProducts(); }});
}

// ── Add to Cart ───────────────────────────────────────────────────────────────
async function addToCart(productId, stock, itemType = null) {
    itemType = itemType || (document.getElementById('orderTypeSelect')?.value === 'mixed'
        ? 'dine_in'
        : (document.getElementById('orderTypeSelect')?.value || 'dine_in'));

    // Check total qty in cart for this product across all types
    const items = window.lastCartPayload?.items ?? [];
    const totalInCart = items
        .filter(i => String(i.product_id) === String(productId))
        .reduce((sum, i) => sum + i.quantity, 0);
    if (totalInCart >= stock) {
        showToast(`Only ${stock} in stock — cannot add more.`);
        return;
    }
    const payload = await postJson(`{{ route('pos.cart.add') }}`, { product_id: productId, quantity: 1, item_type: itemType });
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
    // Qty change
    if (e.target.classList.contains('qty-input')) {
        const stock = Number(e.target.dataset.stock);
        const qty   = Number(e.target.value);
        if (qty > stock) {
            showToast(`Only ${stock} available in stock.`);
            e.target.value = stock;
            return;
        }
        const payload = await postJson(`{{ route('pos.cart.update') }}`, {
            product_id: e.target.dataset.id,
            item_type:  e.target.dataset.type,
            quantity:   qty
        }, 'PATCH');
        renderCart(payload);
    }

    // Item type change — sends new_item_type; backend merges rows if needed
    if (e.target.classList.contains('item-type-select')) {
        const newType = e.target.value;
        const oldType = e.target.dataset.type;
        if (newType === oldType) return;
        // Find quantity of this row from payload
        const items   = window.lastCartPayload?.items ?? [];
        const rowItem = items.find(i => String(i.product_id) === e.target.dataset.id && i.item_type === oldType);
        const payload = await postJson(`{{ route('pos.cart.update') }}`, {
            product_id:    e.target.dataset.id,
            item_type:     oldType,
            new_item_type: newType,
            quantity:      rowItem?.quantity ?? 1
        }, 'PATCH');
        renderCart(payload);
    }
});

cartItemsEl.addEventListener('click', async e => {
    if (!e.target.classList.contains('remove-item')) return;
    e.preventDefault();
    const payload = await postJson(`{{ route('pos.cart.remove') }}`, {
        product_id: e.target.dataset.id,
        item_type:  e.target.dataset.type
    }, 'DELETE');
    renderCart(payload);
});

// ── Clear Cart ───────────────────────────────────────────────────────────────
document.getElementById('orderTypeSelect').addEventListener('change', async e => {
    const type = e.target.value;
    if (type === 'mixed') {
        autoSetOrderType();
        return;
    }

    await setAllCartItemTypes(type);
});

const clearCartBtn = document.getElementById('clearCartBtn');
if (clearCartBtn) {
    clearCartBtn.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to clear all items from the cart?')) return;
        const payload = await postJson(`{{ route('pos.cart.clear') }}`, {}, 'DELETE');
        renderCart(payload);
    });
}

// ── Checkout Modal ────────────────────────────────────────────────────────────
function updateConfirmButtonText() {
    document.getElementById('confirmCheckoutBtn').textContent = 'Place Order';
}

// Cache modal payment DOM refs
const paymentMethodSelect = document.getElementById('paymentMethodSelect');
const amountPaidInput     = document.getElementById('amountPaidInput');
const amountPaidRow       = document.getElementById('amountPaidRow');
const referenceRow        = document.getElementById('referenceRow');
const paymentReference    = document.getElementById('paymentReference');
const changeDisplay       = document.getElementById('changeDisplay');

if (paymentMethodSelect) {
    paymentMethodSelect.addEventListener('change', function () {
        const isCash = this.value === 'cash';
        if (amountPaidRow)  amountPaidRow.style.display  = isCash ? '' : 'none';
        if (referenceRow)   referenceRow.style.display   = isCash ? 'none' : '';
        if (paymentReference) {
            isCash ? paymentReference.removeAttribute('required')
                   : paymentReference.setAttribute('required', 'required');
        }
        updateConfirmButtonText();
    });
}

if (amountPaidInput) {
    amountPaidInput.addEventListener('input', function () {
        const calc   = getCalcTotals();
        const paid   = Number(this.value || 0);
        const change = Math.max(0, Math.round((paid - calc.total) * 100) / 100);
        if (changeDisplay) changeDisplay.textContent = change.toFixed(2);
        updateConfirmButtonText();
    });
}


document.getElementById('checkoutBtn').addEventListener('click', () => {
    const calc     = getCalcTotals();
    const orderType = document.getElementById('orderTypeSelect').value;

    // Reset payment fields inside modal to default state
    if (paymentMethodSelect) paymentMethodSelect.value = 'cash';
    if (amountPaidRow) amountPaidRow.style.display = '';
    if (referenceRow) referenceRow.style.display = 'none';
    if (paymentReference) {
        paymentReference.value = '';
        paymentReference.removeAttribute('required');
    }
    if (amountPaidInput) amountPaidInput.value = '';
    if (changeDisplay) changeDisplay.textContent = '0.00';

    // Populate modal summary
    document.getElementById('modal_customer').textContent =
        document.getElementById('customerName').value || '—';
    document.getElementById('modal_tables').textContent =
        selectedTables.length ? selectedTables.map(t => 'T' + t).join(', ') : '—';
    document.getElementById('modal_type').textContent =
        orderTypeLabel(orderType);
    document.getElementById('modal_subtotal').textContent = money(calc.subtotal);
    document.getElementById('modal_discount').textContent = money(calc.discount);
    document.getElementById('modal_vat').textContent      = money(calc.vat);
    document.getElementById('modal_total').textContent    = money(calc.total);

    // Mixed order breakdown — use item_type stored server-side in each cart item
    const breakdown  = document.getElementById('modal_type_breakdown');
    const items      = window.lastCartPayload?.items ?? [];
    const dineItems  = items.filter(i => (i.item_type ?? 'dine_in') === 'dine_in');
    const takeItems  = items.filter(i => (i.item_type ?? 'dine_in') === 'takeout');

    if (orderType === 'mixed' && dineItems.length && takeItems.length) {
        breakdown.style.display = '';
        document.getElementById('modal_dine_in_items').innerHTML =
            `<strong>🍽 Dine-in:</strong> ${dineItems.map(i => i.name + ' ×' + i.quantity).join(', ')}`;
        document.getElementById('modal_takeout_items').innerHTML =
            `<strong>🥡 Takeout:</strong> ${takeItems.map(i => i.name + ' ×' + i.quantity).join(', ')}`;
    } else {
        breakdown.style.display = 'none';
    }

    updateConfirmButtonText();
    new bootstrap.Modal(document.getElementById('checkoutModal')).show();
});

document.getElementById('confirmCheckoutBtn').addEventListener('click', async () => {
    const btn = document.getElementById('confirmCheckoutBtn');
    const oldText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Processing…';

    try {
        const formData = new FormData(document.getElementById('checkoutForm'));
        const payload  = Object.fromEntries(formData.entries());
        payload.vat_enabled   = formData.get('vat_enabled') ? 1 : 0;
        payload.discount_amount = document.getElementById('discountInput')?.value || 0;
        payload.customer_name = document.getElementById('customerName').value;
        payload.tables        = selectedTables;
        
        // Append modal payment inputs manually
        payload.payment_method    = document.getElementById('paymentMethodSelect').value;
        payload.amount_paid       = document.getElementById('amountPaidInput')?.value || null;
        payload.payment_reference = document.getElementById('paymentReference')?.value || null;

        const res = await fetch(`{{ route('pos.checkout') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();

        if (res.ok && data.success && data.redirect_url) {
            // Clear cart UI immediately before navigation
            cartItemsEl.innerHTML = '<div class="text-muted small py-2">Cart is empty.</div>';
            window.lastCartPayload = null;
            if (data.kitchen_receipt_url) {
                window.open(data.kitchen_receipt_url, 'kitchen_receipt', 'width=400,height=600');
            }
            if (data.status === 'completed') {
                window.location.href = data.redirect_url;
            } else {
                window.location.href = `{{ route('pos.index') }}`; // Reload POS to show occupied tables
            }
        } else {
            // Show validation errors
            const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Checkout failed. Please try again.');
            alert(msg);
            btn.disabled = false;
            btn.textContent = oldText;
        }
    } catch (err) {
        bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
        alert('An error occurred during checkout. Please try again.');
        btn.disabled = false;
        btn.textContent = oldText;
    }
});


// ── Add to Existing Order ──────────────────────────────────────────────────────
let selectedAddOrderId = null;

document.getElementById('addToOrderBtn').addEventListener('click', async () => {
    const items = window.lastCartPayload?.items ?? [];
    if (!items.length) {
        alert('Cart is empty.');
        return;
    }

    const modalEl = document.getElementById('addToOrderModal');
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    const listEl = document.getElementById('occupiedTablesList');
    const detailsEl = document.getElementById('addToOrderDetails');
    const confirmBtn = document.getElementById('confirmAddToOrderBtn');
    
    listEl.innerHTML = '<div class="text-center text-muted py-3">Loading tables...</div>';
    detailsEl.style.display = 'none';
    confirmBtn.disabled = true;
    selectedAddOrderId = null;

    try {
        const res = await fetch(`{{ route('pos.occupied_tables') }}`);
        if (!res.ok) throw new Error('Failed to fetch tables');
        const tables = await res.json();

        if (tables.length === 0) {
            listEl.innerHTML = '<div class="text-center text-muted py-3">No occupied tables with active orders.</div>';
            return;
        }

        let html = '<div class="d-flex flex-wrap gap-2">';
        tables.forEach(t => {
            html += `<button type="button" class="btn btn-outline-danger add-order-table-btn" data-order_id="${t.order_id}" data-info='${JSON.stringify(t)}'>T${t.number}</button>`;
        });
        html += '</div>';
        listEl.innerHTML = html;

        listEl.querySelectorAll('.add-order-table-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                listEl.querySelectorAll('.add-order-table-btn').forEach(b => {
                    b.classList.remove('btn-danger', 'text-white');
                    b.classList.add('btn-outline-danger');
                });
                this.classList.remove('btn-outline-danger');
                this.classList.add('btn-danger', 'text-white');

                const info = JSON.parse(this.dataset.info);
                selectedAddOrderId = info.order_id;

                document.getElementById('addOrder_table').textContent = 'Table(s) ' + info.number;
                document.getElementById('addOrder_customer').textContent = info.customer_name ? `(${info.customer_name})` : '';
                document.getElementById('addOrder_orderId').textContent = 'Order #' + info.order_id;
                document.getElementById('addOrder_currentTotal').textContent = '₱' + info.order_total;
                document.getElementById('addOrder_itemsCount').textContent = info.items_count;

                const cartItemsHtml = items.map(i => `<div>${i.quantity}x ${i.name}</div>`).join('');
                document.getElementById('addOrder_cartSummary').innerHTML = cartItemsHtml;

                detailsEl.style.display = 'block';
                confirmBtn.disabled = false;
            });
        });

    } catch (err) {
        listEl.innerHTML = '<div class="text-center text-danger py-3">Error loading tables.</div>';
    }
});

document.getElementById('confirmAddToOrderBtn').addEventListener('click', async () => {
    if (!selectedAddOrderId) return;
    
    const btn = document.getElementById('confirmAddToOrderBtn');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    try {
        const res = await fetch(`{{ route('pos.add_to_order') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order_id: selectedAddOrderId })
        });
        const data = await res.json();
        
        if (res.ok && data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addToOrderModal')).hide();
            
            // Clear cart UI
            cartItemsEl.innerHTML = '<div class="text-muted small py-2">Cart is empty.</div>';
            window.lastCartPayload = null;
            updateTotals();

            if (data.kitchen_receipt_url) {
                window.open(data.kitchen_receipt_url, 'additional_kitchen_receipt', 'width=400,height=600');
            }
            showToast(data.message || 'Items added to order successfully.');
        } else {
            alert(data.message || 'Failed to add items to order.');
        }
    } catch (err) {
        alert('An error occurred. Please try again.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Add Items to Order';
    }
});

// ── BFCache Fix ───────────────────────────────────────────────────────────────
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
@endpush


