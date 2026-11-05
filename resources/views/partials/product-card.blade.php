@php($catSlug = $product->category?->slug ?? 'plastic')
<a class="product-card" href="{{ route('catalog.show', $product->slug) }}">
    <div class="product-visual" @if($catSlug === 'hardware') data-hw="{{ $product->type }}" @endif>
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
        @elseif($catSlug === 'hardware')
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
                {{ $catSlug === 'plastic' ? __('site.price_m') : __('site.price_pack') }}
            </span>
        </div>
    </div>
</a>
