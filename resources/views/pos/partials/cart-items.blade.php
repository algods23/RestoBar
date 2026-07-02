@if (empty($cart))
    <div class="text-muted">Cart is empty.</div>
@else
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Type</th></tr></thead>
            <tbody>
                @foreach ($cart as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>&#8369;{{ number_format($item['price'], 2) }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($item['item_type'] ?? 'dine_in')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
