<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Baghet')</title>
    @include('partials.seo-meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @stack('head')
    @unless(\App\Support\Seo::isPrivateRoute())
        @include('partials.schema-organization')
    @endunless
    @hasSection('jsonld')
        @yield('jsonld')
    @endif
    @php($gaId = config('services.google_analytics_id'))
    @if($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json($gaId));
        </script>
    @endif
</head>
<body>
<header class="site-header">
    <div class="site-header__inner">
        <a class="logo" href="{{ route('home') }}">
            <img class="logo__img" src="{{ asset('images/logo.svg') }}?v=3" alt="Baghet" width="200" height="76">
        </a>
        <nav class="nav" data-nav>
            <a href="{{ route('home') }}" @class(['is-active' => request()->routeIs('home')])>Baghet</a>
            <a href="{{ route('catalog.plastic') }}" @class(['is-active' => request()->routeIs('catalog.plastic')])>{{ __('site.nav_plastic') }}</a>
            <a href="{{ route('catalog.hardware') }}" @class(['is-active' => request()->routeIs('catalog.hardware')])>{{ __('site.nav_hardware') }}</a>
            <a href="{{ route('contacts') }}" @class(['is-active' => request()->routeIs('contacts')])>{{ __('site.nav_contacts') }}</a>
            <div
                class="site-search"
                data-site-search
                data-search-url="{{ route('catalog.search') }}"
                data-empty-text="{{ __('site.search_empty') }}"
                data-stock-text="{{ __('site.in_stock') }}"
                data-out-text="{{ __('site.out_stock') }}"
            >
                <label class="site-search__field">
                    <span class="sr-only">{{ __('site.search_products') }}</span>
                    <svg viewBox="0 0 20 20" aria-hidden="true">
                        <path d="m14.5 14.5 4 4m-2-9a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="search"
                        autocomplete="off"
                        placeholder="{{ __('site.search_placeholder') }}"
                        aria-label="{{ __('site.search_products') }}"
                        aria-expanded="false"
                        data-search-input
                    >
                    <span class="site-search__loader" aria-hidden="true" data-search-loader></span>
                </label>
                <div class="site-search__results" role="listbox" data-search-results hidden></div>
            </div>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.*')])>{{ __('site.nav_admin') }}</a>
                @else
                    <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard', 'orders.*')])>{{ __('site.nav_cabinet') }}</a>
                @endif
            @endauth
        </nav>
        <div class="header-actions">
            <div class="lang-switch" role="group" aria-label="Language">
                <a class="lang-btn @if(app()->getLocale()==='ru') is-active @endif" href="{{ route('locale.switch', 'ru') }}">RU</a>
                <a class="lang-btn @if(app()->getLocale()==='ro') is-active @endif" href="{{ route('locale.switch', 'ro') }}">RO</a>
            </div>
            @guest
                <a class="btn btn--sm btn--outline" href="{{ route('login') }}">{{ __('site.nav_login') }}</a>
            @else
                @unless(auth()->user()->isAdmin())
                    <a class="btn btn--sm btn--outline" href="{{ route('cart.index') }}">
                        {{ __('site.nav_cart') }}
                        @if(count(session('cart', []))) ({{ array_sum(session('cart', [])) }}) @endif
                    </a>
                @endunless
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn--sm btn--outline" type="submit">{{ __('site.nav_logout') }}</button>
                </form>
            @endguest
            <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<main>
    @if(session('success'))
        <div class="flash flash--success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="flash flash--error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer">
    <div class="site-footer__inner">
        <span>{{ __('site.footer_note') }}</span>
        <a href="{{ route('contacts') }}">{{ __('site.nav_contacts') }}</a>
    </div>
</footer>

<script>
document.querySelector('[data-nav-toggle]')?.addEventListener('click', function () {
    const nav = document.querySelector('[data-nav]');
    const open = nav.classList.toggle('is-open');
    this.setAttribute('aria-expanded', open ? 'true' : 'false');
});

(function () {
    const root = document.querySelector('[data-site-search]');
    if (!root) return;

    const input = root.querySelector('[data-search-input]');
    const results = root.querySelector('[data-search-results]');
    const loader = root.querySelector('[data-search-loader]');
    let timer = null;
    let request = null;
    let activeIndex = -1;

    function close() {
        results.hidden = true;
        results.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
    }

    function setActive(index) {
        const links = Array.from(results.querySelectorAll('.site-search__item'));
        if (!links.length) return;
        activeIndex = (index + links.length) % links.length;
        links.forEach((link, i) => link.classList.toggle('is-active', i === activeIndex));
        links[activeIndex].scrollIntoView({ block: 'nearest' });
    }

    function resultItem(product) {
        const link = document.createElement('a');
        link.className = 'site-search__item';
        link.href = product.url;
        link.setAttribute('role', 'option');

        const media = document.createElement('span');
        media.className = 'site-search__media';
        if (product.image) {
            const image = document.createElement('img');
            image.src = product.image;
            image.alt = '';
            media.appendChild(image);
        }

        const body = document.createElement('span');
        body.className = 'site-search__body';
        const name = document.createElement('strong');
        name.textContent = product.name;
        const meta = document.createElement('span');
        meta.textContent = product.sku + ' · ' + product.category;
        body.append(name, meta);

        const side = document.createElement('span');
        side.className = 'site-search__side';
        const price = document.createElement('strong');
        price.textContent = product.price + ' ' + product.currency;
        const stock = document.createElement('small');
        stock.className = product.in_stock ? 'is-stock' : 'is-out';
        stock.textContent = product.in_stock ? root.dataset.stockText : root.dataset.outText;
        side.append(price, stock);

        link.append(media, body, side);
        return link;
    }

    async function search() {
        const query = input.value.trim();
        if (query.length < 2) {
            request?.abort();
            close();
            return;
        }

        request?.abort();
        request = new AbortController();
        root.classList.add('is-loading');
        loader.hidden = false;

        try {
            const url = new URL(root.dataset.searchUrl, window.location.origin);
            url.searchParams.set('q', query);
            const response = await fetch(url, {
                signal: request.signal,
                headers: { Accept: 'application/json' }
            });
            if (!response.ok) throw new Error('Search failed');

            const data = await response.json();
            results.innerHTML = '';

            if (!data.products.length) {
                const empty = document.createElement('p');
                empty.className = 'site-search__empty';
                empty.textContent = root.dataset.emptyText;
                results.appendChild(empty);
            } else {
                data.products.forEach((product) => results.appendChild(resultItem(product)));
            }

            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            activeIndex = -1;
        } catch (error) {
            if (error.name !== 'AbortError') close();
        } finally {
            root.classList.remove('is-loading');
            loader.hidden = true;
        }
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(search, 220);
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(activeIndex - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            results.querySelectorAll('.site-search__item')[activeIndex]?.click();
        } else if (event.key === 'Escape') {
            close();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) close();
    });
})();
</script>
@stack('scripts')
</body>
</html>
