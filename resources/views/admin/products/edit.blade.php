@extends('layouts.app')

@section('title', __('site.admin_product_edit').' '.$product->sku.' — Baghet')

@section('content')
<section class="account-page account-page--form">
    <a class="back-link" href="{{ route('admin.products.index') }}">← {{ __('site.admin_products_title') }}</a>

    <div class="panel">
        <div class="admin-product-head">
            <div>
                <p class="eyebrow">SKU</p>
                <h1>{{ $product->sku }}</h1>
                <p class="muted">{{ $product->localizedName() }}</p>
            </div>
            @if($product->is_active)
                <a class="btn btn--outline" href="{{ route('catalog.show', $product->slug) }}" target="_blank" rel="noopener">
                    {{ __('site.admin_product_view') }}
                </a>
            @endif
        </div>

        <div class="admin-product-image">
            @if($product->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->localizedName() }}">
            @else
                <img src="{{ asset('images/no-photo.svg') }}" alt="{{ __('site.admin_product_no_image') }}">
            @endif
            <div class="admin-product-image__meta">
                <strong>{{ __('site.admin_product_image') }}</strong>
                <code>{{ $product->image_path ?: __('site.admin_product_no_image') }}</code>
                @if($product->imageUrl())
                    <form method="post" action="{{ route('admin.products.image.destroy', $product) }}"
                          onsubmit='return confirm(@js(__('site.admin_product_remove_image')."?"))'>
                        @csrf
                        @method('DELETE')
                        <button class="btn btn--outline btn--sm" type="submit">{{ __('site.admin_product_remove_image') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <form class="stack-form" method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <label>
                    <span>SKU</span>
                    <input name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="100" autocomplete="off">
                </label>
                <label>
                    <span>{{ __('site.admin_product_name_ru') }}</span>
                    <input name="name_ru" value="{{ old('name_ru', $product->name_ru) }}" required maxlength="255">
                </label>
                <label>
                    <span>{{ __('site.admin_product_name_ro') }}</span>
                    <input name="name_ro" value="{{ old('name_ro', $product->name_ro) }}" maxlength="255">
                </label>
                <label>
                    <span>{{ __('site.admin_product_image_file') }}</span>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                    <small>{{ __('site.admin_product_image_hint') }}</small>
                </label>
                <label>
                    <span>{{ __('site.admin_product_image_url') }}</span>
                    <input name="image_url" value="{{ old('image_url') }}" placeholder="https://…" maxlength="2048">
                </label>
            </div>

            @if($product->imageUrl())
                <label class="check-row">
                    <input type="checkbox" name="remove_image" value="1">
                    <span>{{ __('site.admin_product_remove_image') }}</span>
                </label>
            @endif

            <div class="admin-note">
                <strong>{{ __('site.admin_product_sku_note_title') }}</strong>
                <p>{{ __('site.admin_product_sku_note') }}</p>
            </div>
            <div class="admin-note admin-note--soft">
                <strong>{{ __('site.admin_product_sync_note_title') }}</strong>
                <p>{{ __('site.admin_product_sync_note') }}</p>
            </div>

            <button class="btn btn--primary" type="submit">{{ __('site.save') }}</button>
        </form>

        <div class="admin-product-meta">
            <div><span>{{ __('site.admin_product_category') }}</span><strong>{{ $product->category?->localizedName() ?? '—' }}</strong></div>
            <div><span>{{ __('site.admin_product_price') }}</span><strong>{{ number_format((float) $product->price, 2) }} $</strong></div>
            <div><span>{{ __('site.admin_product_stock') }}</span><strong>{{ $product->stock }}</strong></div>
            <div><span>{{ __('site.admin_product_slug') }}</span><code>{{ $product->slug }}</code></div>
            <div><span>{{ __('site.admin_product_crm_id') }}</span><code>{{ $product->crm_product_id ?: '—' }}</code></div>
        </div>
    </div>
</section>
@endsection
