<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h2>RestoBar {{ ucfirst($reportType) }} Report</h2>
    <p>{{ $from->format('M d, Y') }} to {{ $to->format('M d, Y') }}</p>

    @if($reportType === 'sales')
        <table border="1">
            <tr><th>Total Sales</th><td>{{ number_format($summary['sales'], 2, '.', '') }}</td></tr>
            <tr><th>Orders</th><td>{{ $summary['orders'] }}</td></tr>
            <tr><th>Subtotal</th><td>{{ number_format($summary['subtotal'], 2, '.', '') }}</td></tr>
        </table>

        <h3>Sales Details</h3>
        <table border="1">
            <tr><th>Date/Time</th><th>Order</th><th>Cashier</th><th>Type</th><th>Subtotal</th><th>Discount</th><th>Total</th></tr>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->user?->name ?? 'N/A' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</td>
                    <td>{{ number_format($order->subtotal, 2, '.', '') }}</td>
                    <td>{{ number_format($order->discount_amount, 2, '.', '') }}</td>
                    <td>{{ number_format($order->total_amount, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </table>

        <h3>Best Selling Items</h3>
        <table border="1">
            <tr><th>Product</th><th>Qty Sold</th><th>Sales</th></tr>
            @foreach($bestSellingItems as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->total_quantity }}</td>
                    <td>{{ number_format($item->total_sales, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <table border="1">
            <tr><th>Logs</th><td>{{ $summary['logs'] }}</td></tr>
            <tr><th>Stock In</th><td>{{ $summary['stock_in'] }}</td></tr>
            <tr><th>Stock Out</th><td>{{ $summary['stock_out'] }}</td></tr>
            <tr><th>Adjustments</th><td>{{ $summary['adjustments'] }}</td></tr>
        </table>

        <h3>Inventory Details</h3>
        <table border="1">
            <tr><th>Date/Time</th><th>User</th><th>Product</th><th>Type</th><th>Qty</th><th>Previous</th><th>New</th><th>Notes</th></tr>
            @foreach($inventoryLogs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user?->name ?? 'N/A' }}</td>
                    <td>{{ $log->product?->name ?? 'N/A' }}</td>
                    <td>{{ str_replace('_', ' ', $log->type) }}</td>
                    <td>{{ $log->quantity }}</td>
                    <td>{{ $log->previous_stock }}</td>
                    <td>{{ $log->new_stock }}</td>
                    <td>{{ $log->notes }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
