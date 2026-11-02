@extends('layouts.app')

@section('title', __('site.seo_contacts_title'))
@section('meta_description', __('site.seo_contacts_description'))
@section('canonical', route('contacts'))

@php
    $breadcrumbs = [
        ['name' => 'Baghet', 'url' => route('home')],
        ['name' => __('site.contacts_title'), 'url' => route('contacts')],
    ];
@endphp

@section('jsonld')
@include('partials.schema-breadcrumbs', ['breadcrumbs' => $breadcrumbs])
@endsection

@section('content')
@include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

<section class="section">
    <div class="section__head">
        <h1>{{ __('site.contacts_title') }}</h1>
        <p>{{ __('site.contacts_lead') }}</p>
    </div>
    <div class="contacts-single">
        <ul class="contact-list">
            <li>
                <span class="label">{{ __('site.phone_label') }}</span>
                <a href="tel:+37369295652">+373 69 295 652</a>
            </li>
            <li>
                <span class="label">{{ __('site.email_label') }}</span>
                <a href="mailto:info@baghet.md">info@baghet.md</a>
            </li>
            <li>
                <span class="label">{{ __('site.hours_label') }}</span>
                <span>{{ __('site.hours_value') }}</span>
            </li>
        </ul>
        <div class="seo-copy seo-copy--compact">
            <p>{{ __('site.seo_contacts_text') }}</p>
        </div>
    </div>
</section>
@endsection
