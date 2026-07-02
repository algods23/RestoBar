<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('pos.index');
        }

        $salesQuery = Order::query()->where('status', Order::STATUS_COMPLETED);
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalSalesToday = (clone $salesQuery)->whereDate('created_at', $today)->sum('total_amount');
        $totalSalesThisWeek = (clone $salesQuery)->whereDate('created_at', '>=', $startOfWeek)->sum('total_amount');
        $totalSalesThisMonth = (clone $salesQuery)->whereDate('created_at', '>=', $startOfMonth)->sum('total_amount');

        $ordersToday = (clone $salesQuery)->whereDate('created_at', $today)->count();
        $lowStockProducts = Product::whereColumn('stock', '<=', 'reorder_level')->count();

        $period = $request->get('period', 'day');
        $from = $request->get('from');
        $to = $request->get('to');
        $year = $request->get('year') ?? ($period === 'year' ? date('Y') : null);
        $salesChart = [];

        if ($year) {
            for ($i = 0; $i < 12; $i++) {
                $month = Carbon::createFromDate($year, 1, 1)->addMonths($i);
                $salesChart[$month->format('M Y')] = (clone $salesQuery)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('total_amount');
            }
        } elseif ($from && $to) {
            $startDate = Carbon::parse($from);
            $endDate = Carbon::parse($to);
            $daysDiff = $startDate->diffInDays($endDate) + 1;

            if ($daysDiff <= 31) {
                for ($i = 0; $i < $daysDiff; $i++) {
                    $day = $startDate->copy()->addDays($i);
                    $salesChart[$day->format('M d')] = (clone $salesQuery)->whereDate('created_at', $day)->sum('total_amount');
                }
            } else {
                for ($i = 0; $i <= $daysDiff; $i += 7) {
                    $weekStart = $startDate->copy()->addDays($i);
                    $weekEnd = $weekStart->copy()->addDays(6)->min($endDate);
                    $salesChart[$weekStart->format('M d') . ' - ' . $weekEnd->format('M d')] = (clone $salesQuery)
                        ->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                        ->sum('total_amount');
                }
            }
        } elseif ($period === 'day') {
            for ($i = 23; $i >= 0; $i--) {
                $hour = Carbon::now()->subHours($i);
                $salesChart[$hour->format('H:00')] = (clone $salesQuery)
                    ->whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->endOfHour()])
                    ->sum('total_amount');
            }
        } elseif ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $salesChart[$day->format('M d')] = (clone $salesQuery)->whereDate('created_at', $day)->sum('total_amount');
            }
        } elseif ($period === 'month') {
            for ($i = 29; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $salesChart[$day->format('M d')] = (clone $salesQuery)->whereDate('created_at', $day)->sum('total_amount');
            }
        } elseif ($period === 'year') {
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $salesChart[$month->format('M Y')] = (clone $salesQuery)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('total_amount');
            }
        }

        $bestSellingItems = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->with('product')
            ->whereHas('order', fn ($query) => $query->where('status', Order::STATUS_COMPLETED))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalSalesToday',
            'totalSalesThisWeek',
            'totalSalesThisMonth',
            'ordersToday',
            'lowStockProducts',
            'salesChart',
            'bestSellingItems'
        ));
    }
}
