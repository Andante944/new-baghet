@php
    $listItems = [];
    foreach ($products as $i => $product) {
        $listItems[] = [
            '@type' => 'ListItem',
            'position' => ($products->firstItem() ?? 1) + $i,
            'url' => route('catalog.show', $product->slug),
            'name' => $product->localizedName(),
        ];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $category->localizedName().' — Baghet',
        'description' => $category->slug === 'plastic'
            ? __('site.seo_plastic_description')
            : __('site.seo_hardware_description'),
        'url' => $category->slug === 'plastic' ? route('catalog.plastic') : route('catalog.hardware'),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Baghet',
            'url' => url('/'),
        ],
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => $products->total(),
            'itemListElement' => $listItems,
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}</script>
