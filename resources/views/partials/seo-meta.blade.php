@php
    use App\Support\Seo;

    $pageTitle = trim($__env->yieldContent('title', 'Baghet'));
    $metaDescription = trim($__env->yieldContent('meta_description', __('site.seo_home_description')));
    $ogType = trim($__env->yieldContent('og_type', 'website'));
    $ogImage = trim($__env->yieldContent('og_image', Seo::defaultImage()));
    $canonical = trim($__env->yieldContent('canonical', url()->current()));
    $robots = Seo::robots(trim($__env->yieldContent('robots')) ?: null);
    $isPrivate = Seo::isPrivateRoute();
@endphp

<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

@unless($isPrivate)
    <link rel="alternate" hreflang="ru" href="{{ Seo::langUrl('ru', $canonical) }}">
    <link rel="alternate" hreflang="ro" href="{{ Seo::langUrl('ro', $canonical) }}">
    <link rel="alternate" hreflang="x-default" href="{{ Seo::langUrl('ru', $canonical) }}">
@endunless

<meta property="og:site_name" content="Baghet">
<meta property="og:locale" content="{{ app()->getLocale() === 'ro' ? 'ro_RO' : 'ru_RU' }}">
<meta property="og:locale:alternate" content="{{ app()->getLocale() === 'ro' ? 'ru_RU' : 'ro_RO' }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ Seo::absolute($ogImage) }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ Seo::absolute($ogImage) }}">

<link rel="icon" href="{{ asset('images/logo-mark.png') }}" type="image/png">
