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
    <h2>KITCHEN ORDER</h2>
    
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

    <div class="footer">
        --- END OF ORDER ---
    </div>
</div>

<script>
    // Auto-print and close window for kitchen receipt
    window.onload = function() {
        window.print();
        setTimeout(function() {
            window.close();
        }, 500);
    };
</script>
@endsection
