<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->validateFilters($request);
        [$from, $to] = $this->dateRange($request);
        $reportType = $this->reportType($request);

        return view('reports.index', [
            'reportType' => $reportType,
            'from' => $from,
            'to' => $to,
            'summary' => $reportType === 'inventory'
                ? $this->inventorySummary($from, $to)
                : $this->salesSummary($from, $to),
            'bestSellingItems' => $reportType === 'sales' ? $this->bestSellingItems($from, $to) : collect(),
            'orders' => $reportType === 'sales' ? $this->salesOrders($from, $to)->limit(100)->get() : collect(),
            'inventoryLogs' => $reportType === 'inventory' ? $this->inventoryLogs($from, $to)->limit(150)->get() : collect(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $this->validateFilters($request);
        [$from, $to] = $this->dateRange($request);
        $reportType = $this->reportType($request);

        $data = [
            'reportType' => $reportType,
            'from' => $from,
            'to' => $to,
            'summary' => $reportType === 'inventory'
                ? $this->inventorySummary($from, $to)
                : $this->salesSummary($from, $to),
            'bestSellingItems' => $reportType === 'sales' ? $this->bestSellingItems($from, $to) : collect(),
            'orders' => $reportType === 'sales' ? $this->salesOrders($from, $to)->get() : collect(),
            'inventoryLogs' => $reportType === 'inventory' ? $this->inventoryLogs($from, $to)->get() : collect(),
        ];

        $filename = 'restobar-' . $reportType . '-report-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xls';

        return Response::make(view('reports.excel', $data)->render(), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
        ]);
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
        ];
    }

    private function inventorySummary(Carbon $from, Carbon $to): array
    {
        $logs = Inventory::query()->whereBetween('created_at', [$from, $to]);

        return [
            'logs' => (clone $logs)->count(),
            'stock_in' => (clone $logs)->where('type', 'stock_in')->sum('quantity'),
            'stock_out' => (clone $logs)->whereIn('type', ['stock_out', 'deduction'])->sum('quantity'),
            'adjustments' => (clone $logs)->where('type', 'adjustment')->count(),
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

    private function salesOrders(Carbon $from, Carbon $to)
    {
        return Order::with(['user', 'items.product'])
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->latest();
    }

    private function inventoryLogs(Carbon $from, Carbon $to)
    {
        return Inventory::with(['product', 'user', 'order'])
            ->whereBetween('created_at', [$from, $to])
            ->latest();
    }

    private function dateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from')->toString())->startOfDay()
            : Carbon::today()->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->string('to')->toString())->endOfDay()
            : Carbon::today()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function reportType(Request $request): string
    {
        return $request->string('type')->toString() === 'inventory' ? 'inventory' : 'sales';
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'type' => ['nullable', 'in:sales,inventory'],
        ]);
    }
}
