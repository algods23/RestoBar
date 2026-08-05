@extends('layouts.print')

@section('content')
<style>
    body { background: #fff; margin: 0; padding: 0; }
    .receipt {
        width: 100%;
        max-width: 58mm; /* standard thermal printer width */
        font-family: monospace;
        font-size: 14px; /* slightly larger for kitchen readability */
        padding: 10px;
        box-sizing: border-box;
    }

    h2 { font-size: 20px; text-align: center; margin: 0 0 10px 0; border-bottom: 2px dashed #000; padding-bottom: 5px; }
    
    .meta { font-size: 14px; margin-bottom: 10px; }
    .meta div { margin-bottom: 3px; font-weight: bold; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th { border-bottom: 1px dashed #000; padding: 5px 0; text-align: left; }
    td { padding: 5px 0; vertical-align: top; }
    .qty { text-align: center; font-weight: bold; font-size: 16px; width: 40px; }

    .section-label { font-weight: bold; text-decoration: underline; margin-top: 10px; margin-bottom: 5px; font-size: 16px; }

    .footer { text-align: center; margin-top: 20px; font-size: 12px; }

    /* Auto print styles */
    @media screen {
        body { background: #f0f0f0; padding: 20px; }
        .receipt { background: white; margin: 0 auto; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    }
</style>

<div class="receipt">
    <h2>{{ !empty($additionalOnly) ? 'ADDITIONAL ORDER' : 'KITCHEN ORDER' }}</h2>
    
    <div class="meta">
        <div>Order #: {{ $order->id }}</div>
        <div>Date: {{ $order->created_at->format('H:i A') }}</div>
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
        <div class="section-label">🍽 DINE-IN</div>
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
        <div class="section-label">🥡 TAKE-OUT</div>
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
        <div class="footer">
            No additional items to print.
        </div>
    @endif

</div>

<div style="max-width:58mm; margin:10px auto; font-family:monospace;">
    <label for="printer_select">Printer:</label>
    <select id="printer_select" style="width:100%; margin:6px 0; padding:6px; font-size:14px;"></select>
    <div style="display:flex; gap:6px;">
        <button id="server_print_btn" style="flex:1; padding:8px;">Print to Printer</button>
        <button id="browser_print_btn" style="flex:1; padding:8px;">Browser Print</button>
    </div>
    <div id="print_status" style="margin-top:8px; font-size:12px;"></div>
</div>

<script>
    async function fetchPrinters() {
        const sel = document.getElementById('printer_select');
        sel.disabled = true;
        sel.innerHTML = '<option>Loading...</option>';
        try {
            const res = await fetch('{{ route('printers.list') }}', { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Failed to load printers');
            const printers = await res.json();
            sel.innerHTML = '';
            if (printers.length === 0) {
                sel.innerHTML = '<option>No printers found</option>';
            } else {
                printers.forEach(p => {
                    const o = document.createElement('option'); o.value = p; o.text = p; sel.appendChild(o);
                });
            }
        } catch (e) {
            sel.innerHTML = '<option>Error loading printers</option>';
        } finally {
            sel.disabled = false;
        }
    }

    document.getElementById('server_print_btn').addEventListener('click', async function() {
        const printer = document.getElementById('printer_select').value;
        const status = document.getElementById('print_status');
        status.textContent = 'Sending to printer...';
        try {
            const res = await fetch('{{ route('orders.print', $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ printer: printer, additional: {{ !empty($additionalOnly) ? '1' : '0' }} })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Print failed');
            status.textContent = data.message || 'Printed';
        } catch (e) {
            status.textContent = 'Error: ' + e.message;
        }
    });

    document.getElementById('browser_print_btn').addEventListener('click', function() {
        window.print();
    });

    // Fetch printers on load
    fetchPrinters();
</script>
@endsection
