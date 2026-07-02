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
            ->get();

        $paymentTotals = $transactions
            ->groupBy('method')
            ->map(fn ($payments) => $payments->sum('amount'));

        $salesTotal = $transactions->sum('amount');

        return view('transactions.index', [
            'date' => $date,
            'transactions' => $transactions,
            'paymentTotals' => $paymentTotals,
            'salesTotal' => $salesTotal,
        ]);
    }
}
