@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1">Transactions</h1>
        <div class="text-muted">{{ $date->format('F d, Y') }}</div>
    </div>
    <form method="GET" action="{{ route('transactions.index') }}" class="d-flex gap-2 align-items-end">
        <div>
            <label class="form-label small text-muted mb-1">Date</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date->toDateString() }}">
        </div>
        <button class="btn btn-sm btn-dark">View</button>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 h-100">
            <div class="text-muted small">Total Sales</div>
            <div class="h4 mb-0">&#8369;{{ number_format($salesTotal, 2) }}</div>
        </div>
    </div>
    @foreach(['cash' => 'Cash', 'card' => 'Card', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer'] as $method => $label)
        <div class="col-md-3 col-sm-6">
            <div class="card p-3 h-100">
                <div class="text-muted small">{{ $label }} Received</div>
                <div class="h5 mb-0">&#8369;{{ number_format($paymentTotals->get($method, 0), 2) }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 mb-0 fw-bold">Payments Received</h2>
        <span class="text-muted small">{{ $transactions->count() }} transaction{{ $transactions->count() === 1 ? '' : 's' }}</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Order</th>
                    <th>Cashier</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-end">Amount Received</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('h:i A') }}</td>
                        <td>
                            @if($payment->order)
                                <a href="{{ route('orders.show', $payment->order) }}" class="text-decoration-none">#{{ $payment->order_id }}</a>
                            @else
                                #{{ $payment->order_id }}
                            @endif
                        </td>
                        <td>{{ $payment->user?->name ?? '—' }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($payment->method)) }}</td>
                        <td>{{ $payment->reference ?: '—' }}</td>
                        <td class="text-end fw-semibold">&#8369;{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted text-center py-4">No transactions recorded for this date.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
