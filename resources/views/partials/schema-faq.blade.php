@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => __('site.faq_q1'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('site.faq_a1'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('site.faq_q2'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('site.faq_a2'),
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}</script>
