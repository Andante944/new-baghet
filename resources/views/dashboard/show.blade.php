@extends('layouts.app')

@section('title', $order->number . ' — Baghet')

@section('content')
<section class="account-page">
    <a class="back-link" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">← {{ __('site.orders') }}</a>

    <div class="account-head">
        <div>
            <p class="eyebrow">{{ __('site.order') }}</p>
            <h1>{{ $order->number }}</h1>
            <p class="muted">{{ $order->created_at->format('d.m.Y H:i') }} · {{ $order->user->company_name ?: $order->user->name }}</p>
        </div>
        <span class="badge badge--ok">{{ __('site.status_'.$order->status) }}</span>
    </div>

    <div class="panel">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('site.sku') }}</th>
                        <th>{{ __('site.product') }}</th>
                        <th>{{ __('site.quantity') }}</th>
                        <th>{{ __('site.unit_price') }}</th>
                        <th>{{ __('site.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->sku }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price, 2) }} {{ $order->currency }}</td>
                            <td>{{ number_format($item->total, 2) }} {{ $order->currency }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">{{ __('site.order_total') }}</th>
                        <th>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if($order->notes)
            <div class="order-notes"><strong>{{ __('site.notes') }}:</strong> {{ $order->notes }}</div>
        @endif
    </div>
</section>
@endsection
