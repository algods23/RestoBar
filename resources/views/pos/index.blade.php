@extends('layouts.app')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card p-3 mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><input id="barcodeInput" class="form-control form-control-lg" placeholder="Barcode"></div>
                <div class="col-md-5"><input id="searchInput" class="form-control form-control-lg" placeholder="Search product by name"></div>
                <div class="col-md-2"><button id="searchBtn" class="btn btn-dark btn-lg w-100">Search</button></div>
                <div class="col-md-2"><button id="resetBtn" class="btn btn-outline-secondary btn-lg w-100">Show All</button></div>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Products</h2>
                <span class="text-muted small">Click a card to add it to the cart</span>
            </div>
            <div id="productGrid" class="row g-3">
                @foreach($products as $product)
                    <div class="col-md-4 col-xl-3">
                        <button class="btn btn-light border w-100 text-start add-product-card h-100" data-id="{{ $product->id }}">
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="w-100 rounded mb-2" style="height: 140px; object-fit: cover;">
                            <div class="fw-semibold text-dark text-truncate">{{ $product->name }}</div>
                            <div class="small text-muted text-truncate">{{ $product->category?->name }}</div>
                            <div class="small text-muted">Stock {{ $product->stock }}</div>
                            <div class="fw-bold text-success">₱{{ number_format($product->price, 2) }}</div>
                        </button>
                    </div>
                @endforeach
            </div>
            <div id="searchResults" class="mt-3 row g-2"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-3 sticky-top" style="top: 88px;">
            <h2 class="h5">Current Cart</h2>
            <div id="cartItems">
                @include('pos.partials.cart-items', ['cart' => $cart])
            </div>

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
                    <label class="form-label">Discount</label>
                    <input type="number" step="0.01" name="discount_amount" class="form-control" value="0">
                </div>
                <div class="mb-2" id="pos_amount_paid_row" style="display:none">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" step="0.01" name="amount_paid" id="pos_amount_paid" class="form-control" min="0">
                    <div class="form-text small text-muted">Change: <span id="pos_change">0.00</span></div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="mb-2" id="pos_amount_paid_row">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" step="0.01" name="amount_paid" id="pos_amount_paid" class="form-control" min="0">
                    <div class="form-text small text-muted">Change: <span id="pos_change">0.00</span></div>
                </div>
                <div class="mb-2" id="pos_reference_row" style="display:none">
                    <label class="form-label">Reference</label>
                    <input type="text" name="payment_reference" id="pos_payment_reference" class="form-control">
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" value="1" name="vat_enabled" id="vatEnabled" checked>
                    <label class="form-check-label" for="vatEnabled">Apply 12% VAT</label>
                </div>
                <button class="btn btn-success w-100">Checkout</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const barcodeInput = document.getElementById('barcodeInput');
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const cartItems = document.getElementById('cartItems');
const productGrid = document.getElementById('productGrid');
const resetBtn = document.getElementById('resetBtn');

function money(value) {
    return '₱' + Number(value).toFixed(2);
}

function renderCart(payload) {
    const totals = payload.totals;
    const items = payload.items;
    if (!items.length) {
        cartItems.innerHTML = '<div class="text-muted">Cart is empty.</div>';
        return;
    }

    // store last payload for client-side recalculations
    window.lastCartPayload = payload;

    cartItems.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th></th></tr></thead>
                <tbody>
                    ${items.map(item => `
                        <tr>
                            <td>${item.name}</td>
                            <td style="width: 90px"><input class="form-control form-control-sm qty-input" data-id="${item.product_id}" type="number" min="0" value="${item.quantity}"></td>
                            <td>${money(item.price)}</td>
                            <td><button class="btn btn-sm btn-outline-danger remove-item" data-id="${item.product_id}">x</button></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div id="cartTotals" class="border-top pt-2 small">
            <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="cart_subtotal">${money(totals.subtotal)}</strong></div>
            <div class="d-flex justify-content-between"><span>Discount</span><strong id="cart_discount">${money(totals.discount_amount)}</strong></div>
            <div class="d-flex justify-content-between"><span>VAT</span><strong id="cart_vat">${money(totals.vat_amount)}</strong></div>
            <div class="d-flex justify-content-between fs-5"><span>Total</span><strong id="cart_total">${money(totals.total)}</strong></div>
        </div>`;
}

async function postJson(url, data, method = 'POST') {
    const response = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    });

    if (!response.ok) {
        const payload = await response.json();
        throw payload;
    }

    return response.json();
}

async function searchProducts() {
    const query = barcodeInput.value || searchInput.value;
    const response = await fetch(`{{ route('pos.search') }}?query=${encodeURIComponent(query)}`, {
        headers: { 'Accept': 'application/json' }
    });
    const products = await response.json();

    const html = products.map(product => productCard(product)).join('');
    searchResults.innerHTML = html || '<div class="text-muted">No products found.</div>';
    productGrid.style.display = 'none';
    bindAddButtons(searchResults);
}

function productCard(product) {
    const fallbackImage = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400"><rect width="600" height="400" fill="#f1f5f9"/><rect x="90" y="70" width="420" height="260" rx="28" fill="#e2e8f0"/><circle cx="210" cy="170" r="40" fill="#94a3b8"/><path d="M140 280c34-52 65-78 95-78s61 26 95 78h-190z" fill="#94a3b8"/><path d="M305 280c23-36 47-54 70-54s47 18 70 54H305z" fill="#cbd5e1"/><text x="300" y="360" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" fill="#64748b">No Image</text></svg>');
    return `
        <div class="col-md-4 col-xl-3">
            <button class="btn btn-light border w-100 text-start add-product-card h-100" data-id="${product.id}">
                <img src="${product.image_url || fallbackImage}" alt="${product.name}" class="w-100 rounded mb-2" style="height: 140px; object-fit: cover;">
                <div class="fw-semibold text-dark text-truncate">${product.name}</div>
                <div class="small text-muted text-truncate">${product.category?.name ?? ''}</div>
                <div class="small text-muted">Stock ${product.stock}</div>
                <div class="fw-bold text-success">${money(product.price)}</div>
            </button>
        </div>`;
}

function bindAddButtons(container) {
    // use event delegation for reliability
    container.querySelectorAll('.add-product-card').forEach(button => {
        // ensure buttons are focusable/clickable
        button.setAttribute('type', 'button');
    });
    if (container instanceof Element && container.id === 'productGrid') {
        container.addEventListener('click', async event => {
            const btn = event.target.closest('.add-product-card');
            if (!btn) return;
            const payload = await postJson(`{{ route('pos.cart.add') }}`, { product_id: btn.dataset.id, quantity: 1 });
            renderCart(payload);
        });
    } else {
        container.querySelectorAll('.add-product-card').forEach(button => {
            button.addEventListener('click', async () => {
                const payload = await postJson(`{{ route('pos.cart.add') }}`, { product_id: button.dataset.id, quantity: 1 });
                renderCart(payload);
            });
        });
    }
}

bindAddButtons(document);


document.getElementById('searchBtn').addEventListener('click', searchProducts);
resetBtn.addEventListener('click', () => {
    barcodeInput.value = '';
    searchInput.value = '';
    searchResults.innerHTML = '';
    productGrid.style.display = '';
});
barcodeInput.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        searchProducts();
    }
});
searchInput.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        searchProducts();
    }
});

cartItems.addEventListener('change', async event => {
    if (event.target.classList.contains('qty-input')) {
        const payload = await postJson(`{{ route('pos.cart.update') }}`, {
            product_id: event.target.dataset.id,
            quantity: Number(event.target.value)
        }, 'PATCH');
        renderCart(payload);
    }
});

// Recalculate totals client-side when discount, VAT or amount paid changes
const discountInput = document.querySelector('input[name="discount_amount"]');
const vatCheckbox = document.querySelector('input[name="vat_enabled"]');
const amountPaidInput = document.getElementById('pos_amount_paid');

function computeTotalsFromPayload(payload) {
    const subtotal = Number(payload.totals.subtotal || 0);
    const discount = Number(discountInput?.value || 0);
    const vatEnabled = vatCheckbox?.checked ?? true;
    const vat = vatEnabled ? Math.round((Math.max(0, subtotal - discount) * 0.12) * 100) / 100 : 0;
    const total = Math.max(0, Math.round((subtotal - discount + vat) * 100) / 100);
    return { subtotal, discount, vat, total };
}

function updateTotalsDisplay() {
    const payload = window.lastCartPayload || { totals: { subtotal: 0 } };
    const calc = computeTotalsFromPayload(payload);
    document.getElementById('cart_subtotal').textContent = money(calc.subtotal);
    document.getElementById('cart_discount').textContent = money(calc.discount);
    document.getElementById('cart_vat').textContent = money(calc.vat);
    document.getElementById('cart_total').textContent = money(calc.total);

    // update change due if amount paid present
    if (amountPaidInput) {
        const paid = Number(amountPaidInput.value || 0);
        const change = Math.max(0, Math.round((paid - calc.total) * 100) / 100);
        const changeEl = document.getElementById('pos_change');
        if (changeEl) changeEl.textContent = Number.isFinite(change) ? change.toFixed(2) : '0.00';
    }
}

if (discountInput) discountInput.addEventListener('input', updateTotalsDisplay);
if (vatCheckbox) vatCheckbox.addEventListener('change', updateTotalsDisplay);
if (amountPaidInput) amountPaidInput.addEventListener('input', updateTotalsDisplay);

// ensure totals update after render
const originalRenderCart = renderCart;
renderCart = function(payload) {
    originalRenderCart(payload);
    updateTotalsDisplay();
};

cartItems.addEventListener('click', async event => {
    if (event.target.classList.contains('remove-item')) {
        event.preventDefault();
        const payload = await postJson(`{{ route('pos.cart.remove') }}`, { product_id: event.target.dataset.id }, 'DELETE');
        renderCart(payload);
    }
});

document.getElementById('checkoutForm').addEventListener('submit', async event => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const payload = Object.fromEntries(formData.entries());
    payload.vat_enabled = formData.get('vat_enabled') ? 1 : 0;

    const response = await fetch(`{{ route('pos.checkout') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'text/html,application/xhtml+xml'
        },
        body: JSON.stringify(payload)
    });

    if (response.redirected) {
        window.location.href = response.url;
    } else {
        window.location.reload();
    }
});

// Show/hide reference field when payment method changes or pay_now toggled
const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
const payNowCheckbox = document.getElementById('payNow');
const posReferenceRow = document.getElementById('pos_reference_row');
const posReferenceInput = document.getElementById('pos_payment_reference');

function updatePosReferenceVisibility() {
    if (payNowCheckbox.checked && paymentMethodSelect.value !== 'cash') {
        posReferenceRow.style.display = '';
        posReferenceInput.setAttribute('required', 'required');
    } else {
        posReferenceRow.style.display = 'none';
        posReferenceInput.removeAttribute('required');
    }
}

paymentMethodSelect.addEventListener('change', updatePosReferenceVisibility);
payNowCheckbox.addEventListener('change', updatePosReferenceVisibility);
updatePosReferenceVisibility();
</script>
@endpush
