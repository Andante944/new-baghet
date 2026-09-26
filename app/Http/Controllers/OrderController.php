<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return back()->withErrors(['cart' => __('site.cart_empty')]);
        }

        $products = Product::query()
            ->active()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        if ($products->isEmpty()) {
            return back()->withErrors(['cart' => __('site.cart_empty')]);
        }

        $order = DB::transaction(function () use ($request, $data, $cart, $products) {
            $lines = collect($cart)->map(function ($quantity, $productId) use ($products) {
                $product = $products->get((int) $productId);
                if (! $product) {
                    return null;
                }

                $quantity = (int) $quantity;
                $unitPrice = $product->wholesalePrice();

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => round($unitPrice * $quantity, 2),
                ];
            })->filter();

            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'status' => 'new',
                'subtotal' => $lines->sum('total'),
                'currency' => 'USD',
                'notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->localizedName(),
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total' => $line['total'],
                ]);
            }

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('orders.show', $order)
            ->with('success', __('site.order_created'));
    }
}
