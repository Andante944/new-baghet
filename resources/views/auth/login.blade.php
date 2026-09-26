@extends('layouts.app')

@section('title', __('site.login_title') . ' — Baghet')
@section('meta_description', __('site.login_intro'))
@section('robots', 'noindex, nofollow')

@section('content')
<section class="account-page account-page--narrow">
    <div class="panel">
        <p class="eyebrow">{{ __('site.private_showcase') }}</p>
        <h1>{{ __('site.login_title') }}</h1>
        <p class="muted">{{ __('site.login_intro') }}</p>

        <form class="stack-form" method="post" action="{{ route('login.store') }}">
            @csrf
            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </label>
            <label>
                <span>{{ __('site.password') }}</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <label class="check-row">
                <input type="checkbox" name="remember" value="1">
                <span>{{ __('site.remember_me') }}</span>
            </label>
            <button class="btn btn--primary" type="submit">{{ __('site.nav_login') }}</button>
        </form>
    </div>
</section>
@endsection
