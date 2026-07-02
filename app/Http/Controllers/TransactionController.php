<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->string('date')->toString())
            : Carbon::today();

        $transactions = Payment::with(['order', 'user'])
            ->whereDate('created_at', $date)
            ->latest()
            ->get()
            ->map(function ($payment) {
                $orderTotal = $payment->order ? (float) $payment->order->total_amount : (float) $payment->amount;
                $payment->sales_amount = min((float) $payment->amount, $orderTotal);
                $payment->tendered_amount = (float) $payment->amount;
                $payment->change_amount = max(0, (float) $payment->amount - $payment->sales_amount);

                return $payment;
            });

        $paymentTotals = $transactions
            ->groupBy('method')
            ->map(fn ($payments) => $payments->sum('sales_amount'));

        $salesTotal = $transactions->sum('sales_amount');

        return view('transactions.index', [
            'date' => $date,
            'transactions' => $transactions,
            'paymentTotals' => $paymentTotals,
            'salesTotal' => $salesTotal,
        ]);
    }
}
