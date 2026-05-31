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
    public function index(Request $request): View
    {
        $salesQuery = Order::query()->where('status', Order::STATUS_COMPLETED);
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalSalesToday = (clone $salesQuery)->whereDate('created_at', $today)->sum('total_amount');
        $totalSalesThisWeek = (clone $salesQuery)->whereDate('created_at', '>=', $startOfWeek)->sum('total_amount');
        $totalSalesThisMonth = (clone $salesQuery)->whereDate('created_at', '>=', $startOfMonth)->sum('total_amount');

        $ordersToday = (clone $salesQuery)->whereDate('created_at', $today)->count();
        $lowStockProducts = Product::whereColumn('stock', '<=', 'reorder_level')->count();

        $recentOrders = Order::with('items.product')
            ->latest()
            ->limit(8)
            ->get();

        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $salesChart[$day->format('M d')] = (clone $salesQuery)->whereDate('created_at', $day)->sum('total_amount');
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
            'recentOrders',
            'salesChart',
            'bestSellingItems'
        ));
    }
}
