@extends('layouts.app')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-1">Order #{{ $order->id }}</h1>
            <div class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</div>
        </div>
        <div>
            <a href="{{ route('orders.receipt', $order) }}" class="btn btn-outline-dark">Print Receipt</a>
            @if($order->status === 'pending')
                <button class="btn btn-success pay-btn" data-id="{{ $order->id }}" data-total="{{ $order->total_amount }}">Pay</button>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><strong>Cashier:</strong> {{ $order->user?->name }}</div>
        <div class="col-md-4"><strong>Type:</strong> {{ str_replace('_', ' ', ucfirst($order->order_type)) }}</div>
        <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($order->status) }}</div>
    </div>

    @php
        $typeLabel = fn ($type) => match ($type) {
            'takeout' => 'Takeout',
            'delivery' => 'Delivery',
            default => 'Dine-in',
        };
        $tableColumns = $order->status === 'pending' ? 6 : 5;
    @endphp

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 mb-0 fw-bold">Original Order</h2>
            <span class="small text-muted">&#8369;{{ number_format($originalItems->sum('subtotal'), 2) }}</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                    @if($order->status === 'pending')<th></th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse ($originalItems as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $typeLabel($item->item_type) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>&#8369;{{ number_format($item->price, 2) }}</td>
                        <td>&#8369;{{ number_format($item->subtotal, 2) }}</td>
                        @if($order->status === 'pending')
                            <td class="text-end">
                                <form action="{{ route('orders.items.remove', [$order, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this item? Stock will be restored.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $tableColumns }}" class="text-muted">No original items.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($additionalItems->isNotEmpty())
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h2 class="h6 mb-0 fw-bold text-primary">Additional Order</h2>
                    <div class="small text-muted">Items added after the original order was created.</div>
                </div>
                <span class="small text-muted">&#8369;{{ number_format($additionalItems->sum('subtotal'), 2) }}</span>
            </div>
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        @if($order->status === 'pending')<th></th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($additionalItems as $item)
                        <tr>
                            <td><span class="badge bg-primary me-2">Add-on</span>{{ $item->product?->name }}</td>
                            <td>{{ $typeLabel($item->item_type) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>&#8369;{{ number_format($item->price, 2) }}</td>
                            <td>&#8369;{{ number_format($item->subtotal, 2) }}</td>
                            @if($order->status === 'pending')
                                <td class="text-end">
                                    <form action="{{ route('orders.items.remove', [$order, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this additional item? Stock will be restored.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="ms-auto" style="max-width: 320px;">
        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>&#8369;{{ number_format($order->subtotal, 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>Discount ({{ number_format($order->discount_percentage, 2) }}%)</span><strong>&#8369;{{ number_format($order->discount_amount, 2) }}</strong></div>
        <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>&#8369;{{ number_format($order->total_amount, 2) }}</strong></div>
    </div>
</div>
@include('orders.partials.payment-modal')
@endsection

@push('scripts')
<script>
@if(session('additional_kitchen_receipt_url'))
window.open(@json(session('additional_kitchen_receipt_url')), 'additional_kitchen_receipt', 'width=400,height=600');
@endif
</script>
@endpush
