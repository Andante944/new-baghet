@extends('layouts.app')

@section('title', __('site.cabinet_title') . ' — Baghet')

@section('content')
<section class="account-page">
    <div class="account-head">
        <div>
            <p class="eyebrow">{{ auth()->user()->company_name ?: auth()->user()->name }}</p>
            <h1>{{ __('site.cabinet_title') }}</h1>
            <p class="muted">{{ auth()->user()->email }} @if(auth()->user()->phone) · {{ auth()->user()->phone }} @endif</p>
        </div>
        <a class="btn btn--primary" href="{{ route('catalog.plastic') }}">{{ __('site.make_order') }}</a>
    </div>

    <div class="stats-grid stats-grid--client">
        <a class="stat-card" href="{{ route('cart.index') }}">
            <strong>{{ array_sum(session('cart', [])) }}</strong>
            <span>{{ __('site.cart_items') }}</span>
        </a>
        <div class="stat-card">
            <strong>{{ $orders->total() }}</strong>
            <span>{{ __('site.orders') }}</span>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>{{ __('site.order_history') }}</h2></div>
        @if($orders->isEmpty())
            <div class="empty-state">
                <p>{{ __('site.no_orders') }}</p>
                <a class="btn btn--outline" href="{{ route('catalog.plastic') }}">{{ __('site.make_order') }}</a>
            </div>
        @else
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('site.order_number') }}</th>
                            <th>{{ __('site.order_date') }}</th>
                            <th>{{ __('site.positions') }}</th>
                            <th>{{ __('site.order_total') }}</th>
                            <th>{{ __('site.order_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><a href="{{ route('orders.show', $order) }}">{{ $order->number }}</a></td>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</td>
                                <td><span class="badge badge--ok">{{ __('site.status_'.$order->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pager-space">{{ $orders->links('vendor.pagination.baghet') }}</div>
        @endif
    </div>
</section>
@endsection
