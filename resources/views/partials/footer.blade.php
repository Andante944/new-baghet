<footer class="site-footer">
    <div class="site-footer__grid">
        <div class="site-footer__brand">
            <a class="logo logo--footer" href="{{ route('home') }}">
                <img class="logo__img" src="{{ asset('images/logo.svg') }}?v=3" alt="Baghet" width="200" height="76">
            </a>
            <p>{{ __('site.footer_about') }}</p>
        </div>

        <nav class="site-footer__col" aria-label="{{ __('site.footer_catalog') }}">
            <h2>{{ __('site.footer_catalog') }}</h2>
            <a href="{{ route('catalog.plastic') }}">{{ __('site.nav_plastic') }}</a>
            <a href="{{ route('catalog.hardware') }}">{{ __('site.nav_hardware') }}</a>
            <a href="{{ route('wholesale') }}">{{ __('site.nav_wholesale') }}</a>
        </nav>

        <nav class="site-footer__col" aria-label="{{ __('site.footer_buyers') }}">
            <h2>{{ __('site.footer_buyers') }}</h2>
            <a href="{{ route('wholesale') }}">{{ __('site.nav_wholesale') }}</a>
            <a href="{{ route('contacts') }}">{{ __('site.nav_contacts') }}</a>
            <a href="{{ route('sitemap') }}">{{ __('site.footer_sitemap') }}</a>
            @guest
                <a href="{{ route('login') }}">{{ __('site.nav_login') }}</a>
            @else
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">
                    {{ auth()->user()->isAdmin() ? __('site.nav_admin') : __('site.nav_cabinet') }}
                </a>
            @endguest
        </nav>

        <div class="site-footer__col site-footer__contacts">
            <h2>{{ __('site.footer_contacts') }}</h2>
            <a href="tel:+37369295652">+373 69 295 652</a>
            <a href="mailto:info@baghet.md">info@baghet.md</a>
            <p>{{ __('site.hours_value') }}</p>
        </div>
    </div>

    <div class="site-footer__bottom">
        <span>© {{ date('Y') }} Baghet.md — {{ __('site.footer_rights') }}</span>
        <span>{{ __('site.footer_note') }}</span>
    </div>
</footer>
