@extends('layouts.app')

@php
    $breadcrumbs = [
        ['name' => 'Baghet', 'url' => route('home')],
        ['name' => __('site.wholesale_title'), 'url' => route('wholesale')],
    ];
    $faq = [
        ['q' => __('site.wholesale_faq_q1'), 'a' => __('site.wholesale_faq_a1')],
        ['q' => __('site.wholesale_faq_q2'), 'a' => __('site.wholesale_faq_a2')],
        ['q' => __('site.wholesale_faq_q3'), 'a' => __('site.wholesale_faq_a3')],
        ['q' => __('site.wholesale_faq_q4'), 'a' => __('site.wholesale_faq_a4')],
        ['q' => __('site.wholesale_faq_q5'), 'a' => __('site.wholesale_faq_a5')],
    ];
    $benefits = [
        ['title' => __('site.wholesale_benefit_1_title'), 'text' => __('site.wholesale_benefit_1_text')],
        ['title' => __('site.wholesale_benefit_2_title'), 'text' => __('site.wholesale_benefit_2_text')],
        ['title' => __('site.wholesale_benefit_3_title'), 'text' => __('site.wholesale_benefit_3_text')],
        ['title' => __('site.wholesale_benefit_4_title'), 'text' => __('site.wholesale_benefit_4_text')],
    ];
    $steps = [
        ['title' => __('site.wholesale_step_1_title'), 'text' => __('site.wholesale_step_1_text')],
        ['title' => __('site.wholesale_step_2_title'), 'text' => __('site.wholesale_step_2_text')],
        ['title' => __('site.wholesale_step_3_title'), 'text' => __('site.wholesale_step_3_text')],
    ];
@endphp

@section('title', __('site.seo_wholesale_title'))
@section('meta_description', __('site.seo_wholesale_description'))
@section('canonical', route('wholesale'))

@section('jsonld')
@include('partials.schema-breadcrumbs', ['breadcrumbs' => $breadcrumbs])
@include('partials.schema-faq', ['faq' => $faq])
@endsection

@section('content')
@include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

<section class="section wholesale-hero">
    <div class="section__head">
        <p class="eyebrow">{{ __('site.wholesale_eyebrow') }}</p>
        <h1>{{ __('site.wholesale_title') }}</h1>
        <p>{{ __('site.wholesale_lead') }}</p>
    </div>
    <div class="wholesale-hero__actions">
        <a class="btn btn--primary" href="{{ route('contacts') }}">{{ __('site.wholesale_cta') }}</a>
        <a class="btn btn--outline" href="tel:+37369295652">{{ __('site.wholesale_cta_call') }}</a>
    </div>
</section>

<section class="section wholesale-benefits">
    <div class="section__head">
        <h2>{{ __('site.wholesale_benefits_title') }}</h2>
        <p>{{ __('site.wholesale_benefits_lead') }}</p>
    </div>
    <div class="wholesale-grid">
        @foreach($benefits as $benefit)
            <article class="wholesale-card">
                <h3>{{ $benefit['title'] }}</h3>
                <p>{{ $benefit['text'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="section wholesale-steps">
    <div class="section__head">
        <h2>{{ __('site.wholesale_steps_title') }}</h2>
        <p>{{ __('site.wholesale_steps_lead') }}</p>
    </div>
    <ol class="wholesale-steps__list">
        @foreach($steps as $index => $step)
            <li class="wholesale-step">
                <span class="wholesale-step__num">{{ $index + 1 }}</span>
                <div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['text'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>
</section>

<section class="section section--faq" id="faq">
    <div class="section__head">
        <h2>{{ __('site.wholesale_faq_title') }}</h2>
        <p>{{ __('site.wholesale_faq_lead') }}</p>
    </div>
    @include('partials.faq', ['faq' => $faq])
</section>

<section class="cta-band">
    <div class="cta-band__inner">
        <div>
            <h2>{{ __('site.wholesale_cta_band_title') }}</h2>
            <p>{{ __('site.wholesale_cta_band_text') }}</p>
        </div>
        <div class="cta-band__actions">
            <a class="btn btn--primary" href="tel:+37369295652">+373 69 295 652</a>
            <a class="btn btn--outline" href="mailto:info@baghet.md">info@baghet.md</a>
        </div>
    </div>
</section>
@endsection
