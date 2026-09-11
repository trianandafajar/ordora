<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function admin()
    {
        $stats = [
            'today_revenue' => Order::where('status', 'paid')->whereDate('created_at', today())->sum('total_price'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'active_kasir' => User::where('role', 'kasir')->where('is_active', true)->count(),
            'total_tables' => Table::count(),
        ];

        $recentOrders = Order::latest()->take(5)->get();
        $total = Order::count();
        $total7d = Order::where('status', 'paid')->whereDate('created_at', '>=', now()->subDays(7))->sum('total_price');

        $statusCounts = [
            'pending' => Order::where('status', 'pending')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'served' => Order::where('status', 'served')->count(),
            'paid' => Order::where('status', 'paid')->count(),
        ];

        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $rev = Order::where('status', 'paid')->whereDate('created_at', $date->toDateString())->sum('total_price') ?? 0;
            $revenueData[] = [
                'label' => $date->format('d M'),
                'revenue' => $rev,
            ];
        }
        $maxRev = max(array_column($revenueData, 'revenue')) ?: 1;

        return view('admin.dashboard', compact('stats', 'recentOrders', 'total', 'total7d', 'statusCounts', 'revenueData', 'maxRev'));
    }

    public function reports()
    {
        $stats = [
            'today_revenue' => Order::where('status', 'paid')->whereDate('created_at', today())->sum('total_price'),
            'total_paid_orders' => Order::where('status', 'paid')->count(),
            'total_revenue' => Order::where('status', 'paid')->sum('total_price'),
        ];

        $topProducts = OrderItem::query()
            ->selectRaw('products.name, sum(order_items.quantity) as sold, sum(order_items.subtotal) as revenue')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        return view('admin.reports', compact('stats', 'topProducts'));
    }

    public function kasir(): View
    {
        return view('kasir.dashboard');
    }
}
