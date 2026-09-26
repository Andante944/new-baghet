@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->localizedName(),
        'sku' => $product->sku,
        'description' => $product->localizedDescription() ?: ($product->localizedName().' — Baghet'),
        'image' => array_values(array_filter([$product->imageUrl()])),
        'brand' => [
            '@type' => 'Brand',
            'name' => 'Baghet',
        ],
        'category' => $product->category->localizedName(),
        'offers' => [
            '@type' => 'Offer',
            'url' => route('catalog.show', $product->slug),
            'priceCurrency' => 'USD',
            'price' => number_format((float) $product->displayPrice(), 2, '.', ''),
            'availability' => $product->inStock()
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => [
                '@type' => 'Organization',
                'name' => 'Baghet',
            ],
        ],
    ];

    if ($product->width || $product->color || $product->type) {
        $schema['additionalProperty'] = array_values(array_filter([
            $product->width ? [
                '@type' => 'PropertyValue',
                'name' => __('site.width_label'),
                'value' => __('site.w_'.str_replace('-', '_', $product->width)),
            ] : null,
            $product->color ? [
                '@type' => 'PropertyValue',
                'name' => __('site.color_label'),
                'value' => __('site.color_'.$product->color),
            ] : null,
            $product->type ? [
                '@type' => 'PropertyValue',
                'name' => __('site.type_label'),
                'value' => __('site.type_'.$product->type),
            ] : null,
        ]));
    }
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}</script>
