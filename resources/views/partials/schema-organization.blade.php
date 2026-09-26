@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => ['Organization', 'LocalBusiness'],
        'name' => 'Baghet',
        'url' => url('/'),
        'logo' => asset('images/logo.svg'),
        'image' => asset('images/hero-molding.jpg'),
        'email' => 'info@baghet.md',
        'telephone' => ['+37369295652', '+37360070758'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'str. Cuza Vodă 18/5',
            'addressLocality' => 'Chișinău',
            'addressCountry' => 'MD',
        ],
        'openingHoursSpecification' => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'opens' => '09:00',
            'closes' => '17:00',
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Moldova',
        ],
        'description' => __('site.seo_home_description'),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}</script>
