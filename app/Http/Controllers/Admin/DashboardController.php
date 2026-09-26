<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $clientCount = User::query()->where('role', 'client')->count();
        $orderCount = Order::query()->count();
        $newOrderCount = Order::query()->where('status', 'new')->count();
        $orders = Order::query()
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.dashboard', compact(
            'clientCount',
            'orderCount',
            'newOrderCount',
            'orders',
        ));
    }
}
