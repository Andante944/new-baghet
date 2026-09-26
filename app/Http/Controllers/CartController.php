<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $this->items($request);
        $subtotal = $items->sum('total');

        return view('cart.index', compact('items', 'subtotal'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = min(999, ($cart[$product->id] ?? 0) + $data['quantity']);
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', __('site.cart_added'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $request->session()->get('cart', []);
        if (isset($cart[$product->id])) {
            $cart[$product->id] = $data['quantity'];
            $request->session()->put('cart', $cart);
        }

        return back()->with('success', __('site.cart_updated'));
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', __('site.cart_updated'));
    }

    /**
     * @return Collection<int, object{product: Product, quantity: int, unit_price: float, total: float}>
     */
    private function items(Request $request): Collection
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::query()->whereIn('id', array_keys($cart))->get()->keyBy('id');

        return collect($cart)->map(function ($quantity, $productId) use ($products) {
            $product = $products->get((int) $productId);
            if (! $product) {
                return null;
            }

            $unitPrice = $product->wholesalePrice();

            return (object) [
                'product' => $product,
                'quantity' => (int) $quantity,
                'unit_price' => $unitPrice,
                'total' => round($unitPrice * (int) $quantity, 2),
            ];
        })->filter()->values();
    }
}
