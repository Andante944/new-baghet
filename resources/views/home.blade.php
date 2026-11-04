@extends('layouts.app')

@section('title', __('site.seo_home_title'))
@section('meta_description', __('site.seo_home_description'))
@section('canonical', route('home'))

@php
    $faq = [
        ['q' => __('site.faq_q1'), 'a' => __('site.faq_a1')],
        ['q' => __('site.faq_q2'), 'a' => __('site.faq_a2')],
        ['q' => __('site.faq_q3'), 'a' => __('site.faq_a3')],
        ['q' => __('site.faq_q4'), 'a' => __('site.faq_a4')],
        ['q' => __('site.faq_q5'), 'a' => __('site.faq_a5')],
    ];
@endphp

@section('jsonld')
@include('partials.schema-website')
@include('partials.schema-faq', ['faq' => $faq])
@endsection

@section('content')
<section class="hero">
    <div class="hero__media" aria-hidden="true">
        <img src="{{ asset('images/hero-molding.jpg') }}" alt="{{ __('site.seo_home_image_alt') }}" width="1600" height="900" fetchpriority="high">
    </div>
    <div class="hero__scrim" aria-hidden="true"></div>

    <div class="hero__inner">
        <p class="eyebrow">{{ __('site.brand_tag') }}</p>
        <h1>Baghet</h1>
        <p class="hero__text">{{ __('site.hero_text') }}</p>
        <div class="hero__cta">
            <a class="btn btn--primary" href="{{ route('catalog.plastic') }}">{{ __('site.cta_plastic') }}</a>
            <a class="btn btn--ghost" href="{{ route('catalog.hardware') }}">{{ __('site.cta_hardware') }}</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="section__head">
        <h2>{{ __('site.section_cats') }}</h2>
        <p>{{ __('site.seo_home_intro') }}</p>
    </div>
    <div class="cat-split">
        <a class="cat-panel cat-panel--plastic" href="{{ route('catalog.plastic') }}">
            <h3>{{ __('site.cat_plastic_title') }}</h3>
            <p>{{ __('site.cat_plastic_text') }}</p>
            <span class="btn btn--ghost">{{ __('site.cta_plastic') }}</span>
        </a>
        <a class="cat-panel cat-panel--hardware" href="{{ route('catalog.hardware') }}">
            <h3>{{ __('site.cat_hardware_title') }}</h3>
            <p>{{ __('site.cat_hardware_text') }}</p>
            <span class="btn btn--ghost">{{ __('site.cta_hardware') }}</span>
        </a>
    </div>
</section>

<section class="cta-band">
    <div class="cta-band__inner">
        <div>
            <h2>{{ __('site.cta_login') }}</h2>
            <p>{{ __('site.add_request_hint') }}</p>
        </div>
        <div class="cta-band__actions">
            @guest
                <a class="btn btn--primary" href="{{ route('login') }}">{{ __('site.nav_login') }}</a>
            @else
                <a class="btn btn--primary" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">
                    {{ auth()->user()->isAdmin() ? __('site.nav_admin') : __('site.nav_cabinet') }}
                </a>
            @endguest
            <a class="btn btn--outline" href="{{ route('wholesale') }}">{{ __('site.cta_wholesale') }}</a>
        </div>
    </div>
</section>

<section class="section section--faq" id="faq">
    <div class="section__head">
        <h2>{{ __('site.faq_title') }}</h2>
        <p>{{ __('site.faq_lead') }}</p>
    </div>
    @include('partials.faq', ['faq' => $faq])
</section>
@endsection
