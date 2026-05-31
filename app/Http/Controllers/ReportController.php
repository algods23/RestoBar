<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', [
            'daily' => $this->salesSummary(Carbon::today(), Carbon::today()),
            'weekly' => $this->salesSummary(Carbon::now()->startOfWeek(), Carbon::now()),
            'monthly' => $this->salesSummary(Carbon::now()->startOfMonth(), Carbon::now()),
            'bestSellingItems' => $this->bestSellingItems(Carbon::now()->startOfMonth(), Carbon::now()),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $period = $request->string('period', 'daily')->toString();
        [$from, $to] = match ($period) {
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()],
            default => [Carbon::today(), Carbon::today()],
        };

        $data = [
            'period' => $period,
            'summary' => $this->salesSummary($from, $to),
            'bestSellingItems' => $this->bestSellingItems($from, $to),
            'orders' => Order::with('items.product')
                ->where('status', Order::STATUS_COMPLETED)
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->get(),
        ];

        $pdf = Pdf::loadView('reports.pdf', $data);

        return $pdf->download('restobar-report-' . $period . '.pdf');
    }

    private function salesSummary(Carbon $from, Carbon $to): array
    {
        $orders = Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$from, $to]);

        return [
            'orders' => (clone $orders)->count(),
            'sales' => (clone $orders)->sum('total_amount'),
            'subtotal' => (clone $orders)->sum('subtotal'),
            'vat' => (clone $orders)->sum('vat_amount'),
        ];
    }

    private function bestSellingItems(Carbon $from, Carbon $to): Collection
    {
        return OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(subtotal) as total_sales')
            ->with('product')
            ->whereHas('order', function ($query) use ($from, $to) {
                $query->where('status', Order::STATUS_COMPLETED)
                    ->whereBetween('created_at', [$from, $to]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }
}
