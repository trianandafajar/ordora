<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;

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

        return view('admin.dashboard', compact('stats'));
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

    public function kasir()
    {
        $orders = Order::where('status', '!=', 'paid')
            ->with(['table'])
            ->latest()
            ->get();

        return view('kasir.dashboard', compact('orders'));
    }
}
