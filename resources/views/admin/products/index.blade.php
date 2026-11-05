@extends('layouts.app')

@section('title', __('site.admin_products_title').' — Baghet')

@section('content')
<section class="account-page">
    <div class="account-head">
        <div>
            <a class="back-link" href="{{ route('admin.dashboard') }}">← {{ __('site.nav_admin') }}</a>
            <h1>{{ __('site.admin_products_title') }}</h1>
            <p class="muted">{{ __('site.admin_products_lead') }}</p>
        </div>
    </div>

    <div class="stats-grid">
        <a class="stat-card" href="{{ route('admin.products.index') }}">
            <strong>{{ $total }}</strong>
            <span>{{ __('site.admin_products_total') }}</span>
        </a>
        <a class="stat-card" href="{{ route('admin.products.index', ['image' => 'missing']) }}">
            <strong>{{ $missing }}</strong>
            <span>{{ __('site.admin_products_missing_image') }}</span>
        </a>
    </div>

    <form class="search-form" method="get" action="{{ route('admin.products.index') }}">
        <input
            type="search"
            name="q"
            value="{{ $q }}"
            placeholder="{{ __('site.admin_product_search') }}"
            autocomplete="off"
        >
        <select name="image">
            <option value="all" @selected($image === 'all')>{{ __('site.admin_product_filter_all') }}</option>
            <option value="missing" @selected($image === 'missing')>{{ __('site.admin_product_filter_missing') }}</option>
            <option value="present" @selected($image === 'present')>{{ __('site.admin_product_filter_present') }}</option>
        </select>
        <button class="btn btn--outline" type="submit">{{ __('site.search') }}</button>
    </form>

    <div class="panel">
        <div class="data-table-wrap">
            <table class="data-table admin-products-table">
                <thead>
                    <tr>
                        <th>{{ __('site.admin_product_image') }}</th>
                        <th>SKU</th>
                        <th>{{ __('site.admin_product_name_ru') }}</th>
                        <th>{{ __('site.admin_product_category') }}</th>
                        <th>{{ __('site.admin_product_price') }}</th>
                        <th>{{ __('site.admin_product_stock') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <a class="admin-thumb" href="{{ route('admin.products.edit', $product) }}">
                                    @if($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->localizedName() }}" loading="lazy">
                                    @else
                                        <img src="{{ asset('images/no-photo.svg') }}" alt="{{ __('site.admin_product_no_image') }}" loading="lazy">
                                    @endif
                                </a>
                            </td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>
                                <strong>{{ $product->name_ru }}</strong>
                                @if($product->name_ro && $product->name_ro !== $product->name_ru)
                                    <small>{{ $product->name_ro }}</small>
                                @endif
                            </td>
                            <td>{{ $product->category?->localizedName() ?? '—' }}</td>
                            <td>{{ number_format((float) $product->price, 2) }} $</td>
                            <td>{{ $product->stock }}</td>
                            <td class="admin-row-actions">
                                <a href="{{ route('admin.products.edit', $product) }}">{{ __('site.edit') }}</a>
                                @if($product->is_active)
                                    <a href="{{ route('catalog.show', $product->slug) }}" target="_blank" rel="noopener">{{ __('site.admin_product_view') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">{{ __('site.admin_product_none') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pager-space">{{ $products->links('vendor.pagination.baghet') }}</div>
</section>
@endsection
