@extends('layouts.app')

@php
    $term = $term ?? '';
    $breadcrumbs = [
        ['name' => 'Baghet', 'url' => route('home')],
        ['name' => __('site.search_products'), 'url' => route('catalog.search')],
    ];
@endphp

@section('title', __('site.search_results_title', ['term' => $term]))
@section('meta_description', __('site.search_results_title', ['term' => $term]))
@section('canonical', route('catalog.search'))
@section('robots', 'noindex, follow')

@section('jsonld')
@include('partials.schema-breadcrumbs', ['breadcrumbs' => $breadcrumbs])
@endsection

@section('content')
@include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

<section class="section">
    <div class="section__head">
        <h1>{{ __('site.search_results_title', ['term' => $term]) }}</h1>
        <p>{{ __('site.results') }}: {{ $products->total() }}</p>
    </div>

    <form class="search-page-form" method="get" action="{{ route('catalog.search') }}">
        <input
            type="search"
            name="q"
            value="{{ $term }}"
            placeholder="{{ __('site.search_placeholder') }}"
            aria-label="{{ __('site.search_products') }}"
        >
        <button class="btn btn--primary" type="submit">{{ __('site.search_go') }}</button>
    </form>

    @if($products->isEmpty())
        <p class="empty">{{ __('site.search_nothing', ['term' => $term]) }}</p>
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
</section>
@endsection
