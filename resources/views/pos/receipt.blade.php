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
    .addon-section-label {
        color: #000;
        border-top: 1px dashed #bbb;
        border-bottom: 1px dashed #bbb;
        padding: 4px 0;
        margin-top: 10px;
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

    .receipt-actions {
        position: fixed;
        top: 120px;
        right: 32px;
        width: 220px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        padding: 12px;
        z-index: 20;
    }
    .receipt-actions-title {
        font-size: 12px;
        font-weight: bold;
        color: #555;
        margin-bottom: 8px;
    }
    .receipt-status {
        min-height: 16px;
        margin-top: 8px;
        font-size: 11px;
        line-height: 1.35;
        color: #555;
    }
    .receipt-status.error { color: #b42318; }
    .receipt-status.success { color: #067647; }
    .print-btn,
    .back-btn {
        display: block;
        width: 100%;
        padding: 10px;
        font-size: 13px;
        color: #fff;
        border: none;
        cursor: pointer;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }
    .print-btn { background: #111; }
    .print-btn:hover { background: #333; }
    .back-btn { background: #444; margin-top: 8px; }
    .back-btn:hover { background: #666; }

    @media (max-width: 920px) {
        .receipt-actions {
            position: static;
            max-width: 400px;
            width: auto;
            margin: 12px auto 0;
        }
    }

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
        .addon-section-label { padding: 2px 0 !important; margin-top: 5px !important; }
        .section-subtotal { font-size: 8px !important; }
        .summary { font-size: 9px !important; }
        .summary-row.total { font-size: 11px !important; }
        .receipt-footer { font-size: 8px !important; margin-top: 6px !important; }
        .no-print { display: none !important; }
        hr.dashed { border-top: 1px dashed #000 !important; margin: 4px 0 !important; }
    }
</style>

@php
    $regularItems = $order->items->where('is_additional', false);
    $addonItems = $order->items->where('is_additional', true);
    $typeLabel = fn ($type) => match ($type) {
        'takeout' => 'Takeout',
        'delivery' => 'Delivery',
        default => 'Dine-in',
    };
@endphp

<div class="receipt">
    <div class="receipt-header">
        <h2>RestoBar POS</h2>
        <div class="date">
            Receipt #{{ $order->id }} &nbsp;|&nbsp;
            {{ $order->created_at->format('M d, Y h:i A') }}
        </div>
    </div>

    <hr class="dashed">

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

    <table>
        <thead>
            <tr><th>Item</th><th>Qty</th><th>PHP</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach($regularItems as $item)
                <tr>
                    <td>
                        {{ $item->product?->name ?? 'N/A' }}
                        @if($item->item_type && $item->item_type !== $order->order_type)
                            <span style="font-size: 8px;">({{ $typeLabel($item->item_type) }})</span>
                        @endif
                    </td>
                    <td style="text-align:center;">{{ $item->quantity }}</td>
                    <td style="text-align:right;">{{ number_format($item->price, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($addonItems->isNotEmpty())
        <div class="section-label addon-section-label">ADD-ON</div>
        <table>
            <thead>
                <tr><th>Item</th><th>Qty</th><th>PHP</th><th>Total</th></tr>
            </thead>
            <tbody>
                @foreach($addonItems as $item)
                    <tr>
                        <td>
                            {{ $item->product?->name ?? 'N/A' }}
                            @if($item->item_type && $item->item_type !== $order->order_type)
                                <span style="font-size: 8px;">({{ $typeLabel($item->item_type) }})</span>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:right;">{{ number_format($item->price, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="section-subtotal">
            Add-on subtotal: PHP {{ number_format($addonItems->sum('subtotal'), 2) }}
        </div>
    @endif

    <hr class="dashed">

    <div class="summary">
        <div class="summary-row">
            <span>Subtotal</span>
            <span>PHP {{ number_format($order->subtotal, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Discount ({{ number_format($order->discount_percentage, 2) }}%)</span>
            <span>PHP {{ number_format($order->discount_amount, 2) }}</span>
        </div>
        <hr class="dashed">
        <div class="summary-row total">
            <span>TOTAL</span>
            <span>PHP {{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    <hr class="dashed">
    <div class="receipt-meta" style="font-size:11px;">
        <div>Payment: <strong>{{ str_replace('_', ' ', ucfirst($order->payment_method)) }}</strong></div>
        @if($order->payment_reference)
            <div>Ref: <strong>{{ $order->payment_reference }}</strong></div>
        @endif
    </div>

    <div class="receipt-footer">
        <div>Thank you for your purchase!</div>
        <div style="margin-top:4px;">Powered by RestoBar POS</div>
    </div>
</div>

<div class="receipt-actions no-print">
    <div class="receipt-actions-title">Receipt Actions</div>
    <button id="cashier_print_btn" class="print-btn" type="button">Print</button>
    <button class="back-btn" onclick="window.location.href='{{ route('pos.index') }}'">Back to POS</button>
    <div id="cashier_print_status" class="receipt-status"></div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('cashier_print_btn').addEventListener('click', async function() {
        const status = document.getElementById('cashier_print_status');
        this.disabled = true;
        status.className = 'receipt-status';
        status.textContent = 'Sending to cashier printer...';

        try {
            const res = await fetch('{{ route('orders.print', $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ receipt_type: 'cashier' }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Print failed');

            status.className = 'receipt-status success';
            status.textContent = data.message || 'Printed';
        } catch (e) {
            status.className = 'receipt-status error';
            status.textContent = 'Error: ' + e.message;
        } finally {
            this.disabled = false;
        }
    });
</script>
@endpush
