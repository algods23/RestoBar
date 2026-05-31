<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h1>RestoBar {{ ucfirst($period) }} Report</h1>
    <p>Orders: {{ $summary['orders'] }} | Sales: ₱{{ number_format($summary['sales'], 2) }}</p>
    <h2>Best Selling Items</h2>
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Sales</th></tr></thead>
        <tbody>
            @foreach ($bestSellingItems as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->total_quantity }}</td>
                    <td>₱{{ number_format($item->total_sales, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
