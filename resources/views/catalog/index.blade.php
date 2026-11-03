@extends('layouts.app')

@php
    $catalogRoute = $category->slug === 'plastic' ? route('catalog.plastic') : route('catalog.hardware');
    $hasFilters = filled($widths) || filled($colors) || filled($types) || $sort !== 'default';
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
            <h2>{{ __('site.filter_title') }}</h2>

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
            <div class="catalog-toolbar__side">
                <form method="get" action="{{ $catalogRoute }}" class="catalog-sort">
                    @foreach (['width', 'color', 'type'] as $key)
                        @foreach ((array) request()->input($key, []) as $value)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $value }}">
                        @endforeach
                    @endforeach
                    <label>
                        <span>{{ __('site.filter_sort') }}</span>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="default" @selected($sort === 'default')>{{ __('site.sort_default') }}</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>{{ __('site.sort_price_asc') }}</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>{{ __('site.sort_price_desc') }}</option>
                            <option value="name" @selected($sort === 'name')>{{ __('site.sort_name') }}</option>
                            <option value="new" @selected($sort === 'new')>{{ __('site.sort_new') }}</option>
                        </select>
                    </label>
                </form>
                <p class="results-count">{{ __('site.results') }}: {{ $products->total() }}</p>
            </div>
        </div>

        @if($products->isEmpty())
            <p class="empty">{{ __('site.empty_catalog') }}</p>
        @else
            <div class="product-grid">
                @foreach($products as $product)
                    @include('partials.product-card', ['product' => $product])
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
