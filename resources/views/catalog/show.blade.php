@extends('layouts.app')

@php
    $catalogRoute = $product->category->slug === 'plastic' ? route('catalog.plastic') : route('catalog.hardware');
    $productUrl = route('catalog.show', $product->slug);
    $metaDescription = __('site.seo_product_description', [
        'name' => $product->localizedName(),
        'sku' => $product->sku,
        'category' => $product->category->localizedName(),
    ]);
    $breadcrumbs = [
        ['name' => 'Baghet', 'url' => route('home')],
        ['name' => $product->category->localizedName(), 'url' => $catalogRoute],
        ['name' => $product->localizedName(), 'url' => $productUrl],
    ];
@endphp

@section('title', $product->localizedName() . ' — ' . $product->sku . ' | Baghet')
@section('meta_description', $metaDescription)
@section('canonical', $productUrl)
@section('og_type', 'product')
@section('og_image', $product->imageUrl() ?: \App\Support\Seo::defaultImage())
@section('product_price', number_format((float) $product->displayPrice(), 2, '.', ''))
@section('product_currency', 'USD')
@section('product_availability', $product->inStock() ? 'in stock' : 'out of stock')

@push('head')
    @if($product->imageUrl())
        <link rel="preload" as="image" href="{{ $product->imageUrl() }}" fetchpriority="high">
    @endif
@endpush

@section('jsonld')
@include('partials.schema-breadcrumbs', ['breadcrumbs' => $breadcrumbs])
@include('partials.schema-product')
@endsection

@section('content')
@include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

<div class="product-page">
    <a class="back-link" href="{{ $catalogRoute }}">
        ← {{ __('site.back_catalog') }}
    </a>

    <div class="product-detail">
        <div class="product-detail__media">
            <div class="product-visual">
                @if($product->imageUrl())
                    <img
                        src="{{ $product->imageUrl() }}"
                        alt="{{ $product->localizedName() }} — {{ $product->sku }}"
                        width="640"
                        height="480"
                        loading="eager"
                        decoding="async"
                        style="width:100%;height:100%;object-fit:contain;padding:1.5rem;"
                    >
                @else
                    <img
                        src="{{ asset('images/no-photo.svg') }}"
                        alt="{{ $product->localizedName() }} — фото скоро"
                        width="640"
                        height="480"
                        loading="eager"
                        decoding="async"
                        style="width:100%;height:100%;object-fit:contain;padding:1.5rem;"
                    >
                @endif
            </div>
        </div>
        <div class="product-detail__info">
            <p class="eyebrow">{{ $product->category->localizedName() }}</p>
            <h1>{{ $product->localizedName() }}</h1>
            @if(auth()->user()?->isAdmin())
                <a class="admin-edit-link" href="{{ route('admin.products.edit', $product) }}">
                    {{ __('site.admin_product_edit') }}
                </a>
            @endif
            <div class="product-detail__price-row">
                <span class="price price--lg">{{ number_format($product->displayPrice(), 2) }} $</span>
                <span class="unit">{{ $product->category->slug === 'plastic' ? __('site.price_m') : __('site.price_pack') }}</span>
                @if($product->inStock())
                    <span class="badge badge--ok">{{ __('site.in_stock') }}</span>
                @else
                    <span class="badge badge--off">{{ __('site.out_stock') }}</span>
                @endif
            </div>
            <h2 class="product-detail__h2">{{ __('site.specs_title') }}</h2>
            <ul class="spec-list">
                <li><span>{{ __('site.sku') }}</span><strong>{{ $product->sku }}</strong></li>
                @if($product->width)
                    <li><span>{{ __('site.width_label') }}</span><strong>{{ __('site.w_' . str_replace('-', '_', $product->width)) }}</strong></li>
                @endif
                @if($product->color)
                    <li><span>{{ __('site.color_label') }}</span><strong>{{ __('site.color_' . $product->color) }}</strong></li>
                @endif
                @if($product->type)
                    <li><span>{{ __('site.type_label') }}</span><strong>{{ __('site.type_' . $product->type) }}</strong></li>
                @endif
                <li>
                    <span>{{ __('site.stock_label') }}</span>
                    <strong>{{ $product->stock }} {{ __('site.pcs') }}</strong>
                </li>
            </ul>
            <p class="hint stock-note">{{ __('site.stock_note') }}</p>
            @if($product->descriptionHtml())
                <h2 class="product-detail__h2">{{ __('site.description_title') }}</h2>
                <div class="product-description">{!! $product->descriptionHtml() !!}</div>
            @endif
            @auth
                @unless(auth()->user()->isAdmin())
                    <form class="add-to-cart" method="post" action="{{ route('cart.store', $product) }}">
                        @csrf
                        <label>
                            <span>{{ __('site.quantity') }}</span>
                            <input type="number" name="quantity" value="1" min="1" max="999">
                        </label>
                        <button type="submit" class="btn btn--primary">
                            {{ __('site.add_request') }}
                        </button>
                    </form>
                @endunless
            @endauth
        </div>
    </div>
</div>

@if($related->isNotEmpty())
    <section class="section product-related">
        <div class="section__head">
            <h2>{{ __('site.related_title') }}</h2>
            <p>{{ __('site.related_lead') }}</p>
        </div>
        <div class="product-grid">
            @foreach($related as $relatedProduct)
                @include('partials.product-card', ['product' => $relatedProduct])
            @endforeach
        </div>
    </section>
@endif
@endsection
