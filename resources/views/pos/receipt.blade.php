@extends('layouts.print')

@section('content')
<style>
    body { background: #f0f0f0; }

    .receipt {
        width: 100%;
        max-width: 400px;
        background: #fff;
        padding: 24px;
        margin: 0 auto;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        font-size: 13px;
        line-height: 1.6;
    }

    .receipt-header { text-align: center; margin-bottom: 12px; }
    .receipt-header h2 { font-size: 18px; font-weight: bold; letter-spacing: 1px; }
    .receipt-header .date { font-size: 11px; color: #888; margin-top: 2px; }
    .receipt-meta { font-size: 12px; margin-bottom: 10px; color: #444; }

    hr.dashed { border: none; border-top: 1px dashed #ccc; margin: 10px 0; }

    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    thead tr { border-bottom: 1px dashed #ccc; }
    th {
        padding: 4px 2px;
        text-align: left;
        font-size: 11px;
        color: #888;
        font-weight: normal;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    td { padding: 5px 2px; vertical-align: top; }
    td:nth-child(2), th:nth-child(2) { text-align: center; }
    td:nth-child(3), th:nth-child(3),
    td:nth-child(4), th:nth-child(4) { text-align: right; }

    .section-label {
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #555;
        margin: 8px 0 4px;
    }

    .section-subtotal {
        text-align: right;
        font-size: 11px;
        color: #666;
        padding: 3px 0 6px;
        border-top: 1px dashed #eee;
        margin-top: 2px;
    }

    .summary { width: 100%; font-size: 12px; margin-top: 4px; }
    .summary-row { display: flex; justify-content: space-between; padding: 3px 0; color: #444; }
    .summary-row.total { font-size: 15px; font-weight: bold; color: #000; margin-top: 4px; }

    .receipt-footer { text-align: center; margin-top: 16px; font-size: 11px; color: #888; }

    .print-btn {
        display: block; width: 100%; padding: 10px; margin: 14px 0 0;
        font-size: 13px; background: #111; color: #fff; border: none;
        cursor: pointer; border-radius: 6px; letter-spacing: 0.5px;
    }
    .print-btn:hover { background: #333; }
    .back-btn {
        display: block; width: 100%; padding: 10px; margin: 8px 0 0;
        font-size: 13px; background: #444; color: #fff; border: none;
        cursor: pointer; border-radius: 6px; letter-spacing: 0.5px;
    }
    .back-btn:hover { background: #666; }

    @media print {
        body { background: #fff; }
        .receipt {
            width: 58mm !important; max-width: 58mm !important;
            padding: 2mm !important; margin: 0 !important;
            border-radius: 0 !important; box-shadow: none !important;
            font-size: 10px !important; line-height: 1.4 !important;
        }
        .receipt-header h2 { font-size: 12px !important; }
        .receipt-header .date { font-size: 9px !important; }
        .receipt-meta { font-size: 9px !important; }
        table, th, td { font-size: 9px !important; }
        th { font-size: 8px !important; }
        td { padding: 2px 1px !important; }
        .section-label { font-size: 8px !important; margin: 4px 0 2px !important; }
        .section-subtotal { font-size: 8px !important; }
        .summary { font-size: 9px !important; }
        .summary-row.total { font-size: 11px !important; }
        .receipt-footer { font-size: 8px !important; margin-top: 6px !important; }
        .no-print { display: none !important; }
        hr.dashed { border-top: 1px dashed #000 !important; margin: 4px 0 !important; }
    }
</style>

@php
    $isMixed   = $order->order_type === 'mixed';
    $dineItems = $order->items->filter(fn($i) => ($i->item_type ?? 'dine_in') === 'dine_in');
    $takeItems = $order->items->filter(fn($i) => ($i->item_type ?? 'dine_in') === 'takeout');
@endphp

<div class="receipt">

    {{-- HEADER --}}
    <div class="receipt-header">
        <h2>RestoBar POS</h2>
        <div class="date">
            Receipt #{{ $order->id }} &nbsp;|&nbsp;
            {{ $order->created_at->format('M d, Y h:i A') }}
        </div>
    </div>

    <hr class="dashed">

    {{-- CASHIER / ORDER TYPE --}}
    <div class="receipt-meta">
        <div>Cashier: <strong>{{ $order->user?->name ?? 'N/A' }}</strong></div>
        <div>Type: <strong>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</strong></div>
        @if($order->customer_name)
            <div>Customer: <strong>{{ $order->customer_name }}</strong></div>
        @endif
        @if($order->tables && $order->tables->count())
            <div>Table(s): <strong>{{ $order->tables->pluck('number')->map(fn($t) => 'T'.$t)->join(', ') }}</strong></div>
        @endif
    </div>

    <hr class="dashed">

    {{-- ITEMS --}}
    @if($isMixed && $dineItems->count() && $takeItems->count())

        {{-- DINE-IN SECTION --}}
        <div class="section-label">🍽 Dine-in</div>
        <table>
            <thead>
                <tr><th>Item</th><th>Qty</th><th>₱</th><th>Total</th></tr>
            </thead>
            <tbody>
                @foreach($dineItems as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:right;">{{ number_format($item->price, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="section-subtotal">
            Section subtotal: ₱{{ number_format($dineItems->sum('subtotal'), 2) }}
        </div>

        <hr class="dashed">

        {{-- TAKEOUT SECTION --}}
        <div class="section-label">🥡 Take-out</div>
        <table>
            <thead>
                <tr><th>Item</th><th>Qty</th><th>₱</th><th>Total</th></tr>
            </thead>
            <tbody>
                @foreach($takeItems as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:right;">{{ number_format($item->price, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="section-subtotal">
            Section subtotal: ₱{{ number_format($takeItems->sum('subtotal'), 2) }}
        </div>

    @else
        {{-- NORMAL single-type items --}}
        <table>
            <thead>
                <tr><th>Item</th><th>Qty</th><th>₱</th><th>Total</th></tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:right;">{{ number_format($item->price, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <hr class="dashed">

    {{-- SUMMARY --}}
    <div class="summary">
        <div class="summary-row">
            <span>Subtotal</span>
            <span>₱{{ number_format($order->subtotal, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Discount</span>
            <span>₱{{ number_format($order->discount_amount, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>VAT (12%)</span>
            <span>₱{{ number_format($order->vat_amount, 2) }}</span>
        </div>
        <hr class="dashed">
        <div class="summary-row total">
            <span>TOTAL</span>
            <span>₱{{ number_format($order->total_amount, 2) }}</span>
        </div>
        @if($order->payment_method === 'cash' && $order->amount_paid)
        <hr class="dashed">
        <div class="summary-row">
            <span>Amount Paid</span>
            <span>₱{{ number_format($order->amount_paid, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Change</span>
            <span>₱{{ number_format(max(0, $order->amount_paid - $order->total_amount), 2) }}</span>
        </div>
        @endif
    </div>

    {{-- PAYMENT METHOD --}}
    <hr class="dashed">
    <div class="receipt-meta" style="font-size:11px;">
        <div>Payment: <strong>{{ str_replace('_', ' ', ucfirst($order->payment_method)) }}</strong></div>
        @if($order->payment_reference)
            <div>Ref: <strong>{{ $order->payment_reference }}</strong></div>
        @endif
    </div>

    {{-- FOOTER --}}
    <div class="receipt-footer">
        <div>Thank you for your purchase!</div>
        <div style="margin-top:4px;">Powered by RestoBar POS</div>
    </div>

</div>

{{-- BUTTONS --}}
<div class="no-print" style="max-width:400px;margin:0 auto;">
    <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
    <button class="back-btn" onclick="history.back()">← Back</button>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('load', () => {
        setTimeout(() => window.print(), 500);
    });
</script>
@endpush