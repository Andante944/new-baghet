@extends('layouts.app')

@section('title', __('site.cart_title') . ' — Baghet')

@section('content')
<section class="account-page">
    <div class="account-head">
        <div>
            <p class="eyebrow">{{ __('site.nav_cabinet') }}</p>
            <h1>{{ __('site.cart_title') }}</h1>
        </div>
        <a class="btn btn--outline" href="{{ route('catalog.plastic') }}">{{ __('site.continue_shopping') }}</a>
    </div>

    @if($items->isEmpty())
        <div class="panel empty-state">
            <p>{{ __('site.cart_empty') }}</p>
            <a class="btn btn--primary" href="{{ route('catalog.plastic') }}">{{ __('site.make_order') }}</a>
        </div>
    @else
        <div class="checkout-grid">
            <div class="panel">
                @foreach($items as $item)
                    <div class="cart-line">
                        <a class="cart-line__image" href="{{ route('catalog.show', $item->product->slug) }}">
                            @if($item->product->imageUrl())
                                <img src="{{ $item->product->imageUrl() }}" alt="{{ $item->product->localizedName() }}">
                            @endif
                        </a>
                        <div class="cart-line__main">
                            <a href="{{ route('catalog.show', $item->product->slug) }}"><strong>{{ $item->product->localizedName() }}</strong></a>
                            <span>{{ $item->product->sku }} · {{ number_format($item->unit_price, 2) }} USD</span>
                        </div>
                        <form class="cart-line__quantity" method="post" action="{{ route('cart.update', $item->product) }}">
                            @csrf
                            @method('patch')
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="999">
                            <button class="link-button" type="submit">{{ __('site.update') }}</button>
                        </form>
                        <strong class="cart-line__total">{{ number_format($item->total, 2) }} USD</strong>
                        <form method="post" action="{{ route('cart.destroy', $item->product) }}">
                            @csrf
                            @method('delete')
                            <button class="link-button link-button--danger" type="submit">{{ __('site.remove') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <aside class="panel checkout-summary">
                <h2>{{ __('site.order_summary') }}</h2>
                <div class="summary-total">
                    <span>{{ __('site.order_total') }}</span>
                    <strong>{{ number_format($subtotal, 2) }} USD</strong>
                </div>
                <p class="hint">{{ __('site.stock_note') }}</p>
                <form class="stack-form" method="post" action="{{ route('orders.store') }}">
                    @csrf
                    <label>
                        <span>{{ __('site.notes') }}</span>
                        <textarea name="notes" rows="4" maxlength="2000" placeholder="{{ __('site.notes_placeholder') }}">{{ old('notes') }}</textarea>
                    </label>
                    <button class="btn btn--primary" type="submit">{{ __('site.submit_order') }}</button>
                </form>
            </aside>
        </div>
    @endif
</section>
@endsection
