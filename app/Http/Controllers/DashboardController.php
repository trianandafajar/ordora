<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function superAdmin()
    {
        $stats = [
            'total_restaurants' => Restaurant::count(),
            'active_restaurants' => Restaurant::where('is_active', true)->count(),
            'suspended_restaurants' => Restaurant::where('is_active', false)->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_cashiers' => User::where('role', 'kasir')->count(),
        ];

        $recentRestaurants = Restaurant::with('owner')->latest()->take(10)->get();

        $growthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $growthData[] = [
                'label' => $month->format('M'),
                'count' => Restaurant::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        }

        $topRevenue = Restaurant::withSum(['orders' => function ($query) {
            $query->whereNotNull('paid_at');
        }], 'total_price')
            ->orderByDesc('orders_sum_total_price')
            ->limit(5)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recentRestaurants', 'growthData', 'topRevenue'));
    }

    public function admin()
    {
        $stats = [
            'today_revenue' => Order::whereNotNull('paid_at')->whereDate('paid_at', today())->sum('total_price'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'active_kasir' => User::where('role', 'kasir')->where('is_active', true)->count(),
            'total_tables' => Table::count(),
        ];

        $recentOrders = Order::latest()->take(5)->get();
        $total = Order::count();
        $total7d = Order::whereNotNull('paid_at')->whereDate('paid_at', '>=', now()->subDays(7))->sum('total_price');

        $statusCounts = [
            'pending' => Order::where('status', 'pending')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'served' => Order::where('status', 'served')->count(),
            'paid' => Order::whereNotNull('paid_at')->count(),
        ];

        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $rev = Order::whereNotNull('paid_at')->whereDate('paid_at', $date->toDateString())->sum('total_price') ?? 0;
            $revenueData[] = [
                'label' => $date->format('d M'),
                'revenue' => $rev,
            ];
        }
        $maxRev = max(array_column($revenueData, 'revenue')) ?: 1;

        return view('admin.dashboard', compact('stats', 'recentOrders', 'total', 'total7d', 'statusCounts', 'revenueData', 'maxRev'));
    }

    public function reports(Request $request): View
    {
        $startDate = $request->get('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', today()->format('Y-m-d'));

        $paidQuery = Order::whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate);

        $stats = [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'period_revenue' => (clone $paidQuery)->sum('total_price'),
            'period_paid_orders' => (clone $paidQuery)->count(),
            'total_revenue' => Order::whereNotNull('paid_at')->sum('total_price'),
            'aov' => 0,
        ];
        $stats['aov'] = $stats['period_paid_orders'] > 0
            ? round($stats['period_revenue'] / $stats['period_paid_orders'], 2)
            : 0;

        $topProducts = OrderItem::query()
            ->selectRaw('products.name, sum(order_items.quantity) as sold, sum(order_items.subtotal) as revenue')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereHas('order', fn ($q) => $q->where('status', 'paid')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate))
            ->groupBy('products.name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        $orderHistory = Order::whereNotNull('paid_at')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with('table', 'user')
            ->latest()
            ->get();

        return view('admin.reports.index', compact('stats', 'topProducts', 'orderHistory', 'startDate', 'endDate'));
    }

    public function pdf(Request $request)
    {
        $startDate = $request->get('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', today()->format('Y-m-d'));

        $paidQuery = Order::whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate);

        $stats = [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'period_revenue' => (clone $paidQuery)->sum('total_price'),
            'period_paid_orders' => (clone $paidQuery)->count(),
            'total_revenue' => Order::whereNotNull('paid_at')->sum('total_price'),
            'aov' => 0,
        ];
        $stats['aov'] = $stats['period_paid_orders'] > 0
            ? round($stats['period_revenue'] / $stats['period_paid_orders'], 2)
            : 0;

        $topProducts = OrderItem::query()
            ->selectRaw('products.name, sum(order_items.quantity) as sold, sum(order_items.subtotal) as revenue')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereHas('order', fn ($q) => $q->where('status', 'paid')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate))
            ->groupBy('products.name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        $orderHistory = Order::whereNotNull('paid_at')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with('table', 'user')
            ->latest()
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf', compact('stats', 'topProducts', 'orderHistory', 'startDate', 'endDate'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-'.$startDate.'_to_'.$endDate.'.pdf');
    }

    public function kasir(): View
    {
        return view('kasir.dashboard');
    }

    public function history(): View
    {
        return view('kasir.history');
    }
}
