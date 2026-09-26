<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return view('dashboard.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless(
            $order->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $order->load('items', 'user');

        return view('dashboard.show', compact('order'));
    }
}
