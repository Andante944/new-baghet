@extends('layouts.app')

@section('title', __('site.admin_title') . ' — Baghet')

@section('content')
<section class="account-page">
    <div class="account-head">
        <div>
            <p class="eyebrow">{{ __('site.nav_admin') }}</p>
            <h1>{{ __('site.admin_title') }}</h1>
        </div>
        <a class="btn btn--primary" href="{{ route('admin.clients.create') }}">{{ __('site.client_add') }}</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><strong>{{ $clientCount }}</strong><span>{{ __('site.clients') }}</span></div>
        <div class="stat-card"><strong>{{ $orderCount }}</strong><span>{{ __('site.orders') }}</span></div>
        <div class="stat-card"><strong>{{ $newOrderCount }}</strong><span>{{ __('site.orders_new') }}</span></div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <h2>{{ __('site.latest_orders') }}</h2>
            <a href="{{ route('admin.clients.index') }}">{{ __('site.manage_clients') }} →</a>
        </div>
        @if($orders->isEmpty())
            <p class="muted">{{ __('site.no_orders') }}</p>
        @else
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('site.order_number') }}</th>
                            <th>{{ __('site.client') }}</th>
                            <th>{{ __('site.order_date') }}</th>
                            <th>{{ __('site.order_total') }}</th>
                            <th>{{ __('site.order_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><a href="{{ route('orders.show', $order) }}">{{ $order->number }}</a></td>
                                <td>{{ $order->user->company_name ?: $order->user->name }}</td>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</td>
                                <td><span class="badge badge--ok">{{ __('site.status_'.$order->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endsection
