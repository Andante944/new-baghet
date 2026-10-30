@extends('layouts.app')

@php
    $catalogRoute = $category->slug === 'plastic' ? route('catalog.plastic') : route('catalog.hardware');
    $hasFilters = filled($widths) || filled($colors) || filled($types);
    $canonical = $hasFilters
        ? $catalogRoute
        : ($products->currentPage() > 1 ? $products->url($products->currentPage()) : $catalogRoute);
    $seoDescription = $category->slug === 'plastic'
        ? __('site.seo_plastic_description')
        : __('site.seo_hardware_description');
    $seoTitle = $category->slug === 'plastic'
        ? __('site.seo_plastic_title')
        : __('site.seo_hardware_title');
    $breadcrumbs = [
        ['name' => 'Baghet', 'url' => route('home')],
        ['name' => $category->localizedName(), 'url' => $catalogRoute],
    ];
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonical)
@section('robots', $hasFilters ? 'noindex, follow' : 'index, follow')

@section('jsonld')
@include('partials.schema-breadcrumbs', ['breadcrumbs' => $breadcrumbs])
@include('partials.schema-collection')
@endsection

@push('head')
    @if($products->previousPageUrl())
        <link rel="prev" href="{{ $products->previousPageUrl() }}">
    @endif
    @if($products->nextPageUrl())
        <link rel="next" href="{{ $products->nextPageUrl() }}">
    @endif
@endpush

@section('content')
@include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

<div class="catalog-layout">
    <aside>
        <form class="filters" method="get">
            <h2>{{ __('site.section_cats') }}</h2>

            @if($category->slug === 'plastic')
                @if($availableWidths->isNotEmpty())
                    <fieldset class="filter-group">
                        <legend>{{ __('site.filter_width') }}</legend>
                        @foreach($availableWidths as $w)
                            @php $wKey = 'site.w_' . str_replace('-', '_', $w); @endphp
                            <label>
                                <input type="checkbox" name="width[]" value="{{ $w }}" @checked(in_array($w, $widths, true))>
                                <span>{{ __($wKey) === $wKey ? $w : __($wKey) }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                @endif

                @if($availableColors->isNotEmpty())
                    <fieldset class="filter-group">
                        <legend>{{ __('site.filter_color') }}</legend>
                        @foreach($availableColors as $c)
                            @php $cKey = 'site.color_' . $c; @endphp
                            <label>
                                <input type="checkbox" name="color[]" value="{{ $c }}" @checked(in_array($c, $colors, true))>
                                <span>{{ __($cKey) === $cKey ? $c : __($cKey) }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                @endif
            @else
                @if($availableTypes->isNotEmpty())
                    <fieldset class="filter-group">
                        <legend>{{ __('site.filter_type') }}</legend>
                        @foreach($availableTypes as $t)
                            @php $tKey = 'site.type_' . $t; @endphp
                            <label>
                                <input type="checkbox" name="type[]" value="{{ $t }}" @checked(in_array($t, $types, true))>
                                <span>{{ __($tKey) === $tKey ? $t : __($tKey) }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                @endif
            @endif

            @php
                $hasFilterGroups = $category->slug === 'plastic'
                    ? ($availableWidths->isNotEmpty() || $availableColors->isNotEmpty())
                    : $availableTypes->isNotEmpty();
            @endphp

            @if($hasFilterGroups)
            <div style="display:flex;gap:.5rem;margin-top:1rem;flex-wrap:wrap">
                <button class="btn btn--primary btn--sm" type="submit">{{ __('site.apply_filters') }}</button>
                <a class="btn btn--outline btn--sm" href="{{ $catalogRoute }}">{{ __('site.reset_filters') }}</a>
            </div>
            @endif
        </form>
    </aside>

    <section>
        <div class="catalog-toolbar">
            <div>
                <h1>{{ $category->localizedName() }}</h1>
                <p>{{ $category->slug === 'plastic' ? __('site.catalog_plastic_lead') : __('site.catalog_hardware_lead') }}</p>
            </div>
            <p class="results-count">{{ __('site.results') }}: {{ $products->total() }}</p>
        </div>

        @if($products->isEmpty())
            <p class="empty">{{ __('site.empty_catalog') }}</p>
        @else
            <div class="product-grid">
                @foreach($products as $product)
                    <a class="product-card" href="{{ route('catalog.show', $product->slug) }}">
                        <div class="product-visual" @if($category->slug==='hardware') data-hw="{{ $product->type }}" @endif>
                            @if($product->imageUrl())
                                <img
                                    src="{{ $product->imageUrl() }}"
                                    alt="{{ $product->localizedName() }} — {{ $product->sku }}"
                                    width="320"
                                    height="240"
                                    loading="lazy"
                                    decoding="async"
                                    style="width:100%;height:100%;object-fit:contain;padding:1rem;"
                                >
                            @elseif($category->slug === 'hardware')
                                <div class="hw-icon"></div>
                            @else
                                <div class="profile-bar profile-bar--outer" style="--tone:#b08d57;background:var(--brass)"></div>
                            @endif
                        </div>
                        <div class="product-meta">
                            <div class="product-meta__top">
                                <span class="sku">{{ $product->sku }}</span>
                                @if($product->inStock())
                                    <span class="badge badge--ok">{{ __('site.in_stock') }}</span>
                                @else
                                    <span class="badge badge--off">{{ __('site.out_stock') }}</span>
                                @endif
                            </div>
                            <div class="product-meta__bottom">
                                <span class="price">{{ number_format($product->displayPrice(), 2) }} $</span>
                                <span class="unit">
                                    {{ $category->slug === 'plastic' ? __('site.price_m') : __('site.price_pack') }}
                                    @guest · {{ __('site.price_retail_short') }}@endguest
                                    @auth · {{ __('site.price_wholesale_short') }}@endauth
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div style="margin-top:2rem">
                {{ $products->links('vendor.pagination.baghet') }}
            </div>
        @endif

        <div class="seo-copy">
            <h2>{{ $category->slug === 'plastic' ? __('site.seo_plastic_h2') : __('site.seo_hardware_h2') }}</h2>
            <p>{{ $category->slug === 'plastic' ? __('site.seo_plastic_text') : __('site.seo_hardware_text') }}</p>
        </div>
    </section>
</div>
@endsection
