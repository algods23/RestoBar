@extends('layouts.print')

@section('content')
<style>
    body { background: #fff; margin: 0; padding: 0; }
    .receipt {
        width: 100%;
        max-width: 58mm;
        font-family: monospace;
        font-size: 14px;
        padding: 10px;
        box-sizing: border-box;
    }

    h2 {
        font-size: 20px;
        text-align: center;
        margin: 0 0 10px 0;
        border-bottom: 2px dashed #000;
        padding-bottom: 5px;
    }

    .meta { font-size: 14px; margin-bottom: 10px; }
    .meta div { margin-bottom: 3px; font-weight: bold; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th { border-bottom: 1px dashed #000; padding: 5px 0; text-align: left; }
    td { padding: 5px 0; vertical-align: top; }
    .qty { text-align: center; font-weight: bold; font-size: 16px; width: 40px; }

    .section-label {
        font-weight: bold;
        text-decoration: underline;
        margin-top: 10px;
        margin-bottom: 5px;
        font-size: 16px;
    }

    .footer { text-align: center; margin-top: 20px; font-size: 12px; }

    .receipt-actions {
        width: 100%;
        max-width: 58mm;
        margin: 12px auto 0;
        padding: 10px;
        background: #fff;
        border: 1px solid #d6d6d6;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        font-family: Arial, sans-serif;
        font-size: 12px;
        box-sizing: border-box;
    }

    .receipt-actions-title {
        margin-bottom: 8px;
        font-weight: 700;
        color: #111;
    }

    .receipt-actions-row {
        display: flex;
        gap: 6px;
    }

    .receipt-actions button,
    .receipt-actions a {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px;
        border: 1px solid #111;
        background: #111;
        color: #fff;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        font-size: 12px;
    }

    .receipt-actions a {
        margin-top: 6px;
        background: #fff;
        color: #111;
    }

    .receipt-actions button:disabled {
        cursor: wait;
        opacity: 0.7;
    }

    .receipt-status {
        margin-top: 8px;
        min-height: 16px;
        color: #333;
        line-height: 1.35;
    }

    .receipt-status.error { color: #b42318; }
    .receipt-status.success { color: #067647; }

    @media screen {
        body { background: #f0f0f0; padding: 20px; }
        .receipt { background: white; margin: 0 auto; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    }

    @media print {
        .receipt-actions { display: none !important; }
    }
</style>

<div class="receipt">
    <h2>{{ !empty($additionalOnly) ? 'ADDITIONAL ORDER' : 'KITCHEN ORDER' }}</h2>

    <div class="meta">
        <div>Order #: {{ $order->id }}</div>
        <div>Date: {{ $order->created_at->format('h:i A') }}</div>
        @if($order->customer_name)
            <div>Customer: {{ $order->customer_name }}</div>
        @endif
        @if($order->tables && $order->tables->count())
            <div>Table(s): {{ $order->tables->pluck('number')->map(fn($t) => 'T'.$t)->join(', ') }}</div>
        @endif
    </div>

    @php
        $dineItems = $order->items->filter(fn($i) => ($i->item_type ?? 'dine_in') === 'dine_in');
        $takeItems = $order->items->filter(fn($i) => ($i->item_type ?? 'dine_in') === 'takeout');
        $deliveryItems = $order->items->filter(fn($i) => ($i->item_type ?? 'dine_in') === 'delivery');
    @endphp

    @if($dineItems->count())
        <div class="section-label">DINE-IN</div>
        <table>
            <thead><tr><th>Qty</th><th>Item</th></tr></thead>
            <tbody>
                @foreach($dineItems as $item)
                    <tr>
                        <td class="qty">{{ $item->quantity }}x</td>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($takeItems->count())
        <div class="section-label">TAKE-OUT</div>
        <table>
            <thead><tr><th>Qty</th><th>Item</th></tr></thead>
            <tbody>
                @foreach($takeItems as $item)
                    <tr>
                        <td class="qty">{{ $item->quantity }}x</td>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($deliveryItems->count())
        <div class="section-label">DELIVERY</div>
        <table>
            <thead><tr><th>Qty</th><th>Item</th></tr></thead>
            <tbody>
                @foreach($deliveryItems as $item)
                    <tr>
                        <td class="qty">{{ $item->quantity }}x</td>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($order->items->isEmpty())
        <div class="footer">No additional items to print.</div>
    @endif
</div>

<div class="receipt-actions">
    <div class="receipt-actions-title">Kitchen Printer</div>
    <div class="receipt-actions-row">
        <button id="server_print_btn" type="button">Print</button>
        <button id="browser_print_btn" type="button">Browser</button>
    </div>
    <div class="receipt-actions-row">
        <a href="{{ route('settings.index') }}" target="_blank" rel="noopener">Printer Setup</a>
    </div>
    <div id="print_status" class="receipt-status"></div>
</div>

<script>
    document.getElementById('server_print_btn').addEventListener('click', async function() {
        const status = document.getElementById('print_status');
        const button = this;
        button.disabled = true;
        status.className = 'receipt-status';
        status.textContent = 'Sending to printer...';

        try {
            const res = await fetch('{{ route('orders.print', $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ additional: {{ !empty($additionalOnly) ? '1' : '0' }} })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Print failed');

            status.className = 'receipt-status success';
            status.textContent = data.message || 'Printed';
        } catch (e) {
            status.className = 'receipt-status error';
            status.textContent = 'Error: ' + e.message;
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('browser_print_btn').addEventListener('click', function() {
        window.print();
    });
</script>
@endsection
