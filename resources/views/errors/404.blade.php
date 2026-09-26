<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('site.e404_title') }} — Baghet</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('images/logo-mark.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/error.css') }}">
</head>
<body>
<div class="gallery" data-gallery>
    <div class="motes" aria-hidden="true">
        @for($i = 0; $i < 14; $i++)
            <i style="
                --top: {{ 30 + ($i * 37) % 65 }}%;
                --left: {{ 6 + ($i * 61) % 88 }}%;
                --size: {{ 2 + $i % 4 }}px;
                --dx: {{ ($i % 2 ? 1 : -1) * (3 + $i % 5) }}vw;
                --dur: {{ 16 + ($i * 7) % 14 }}s;
                --delay: -{{ ($i * 3) % 16 }}s;
            "></i>
        @endfor
    </div>

    <div class="gallery__bar">
        <a class="logo" href="{{ route('home') }}">
            <img class="logo__img" src="{{ asset('images/logo.svg') }}?v=3" alt="Baghet" width="200" height="76">
        </a>
    </div>

    <div class="gallery__stage">
        <div class="hanger" aria-hidden="true">
            <span class="hanger__nail"></span>
            <svg class="hanger__wire" viewBox="0 0 320 60" preserveAspectRatio="none">
                <path d="M40 58 L160 8 L280 58"/>
            </svg>
        </div>

        <div class="frame">
            <div class="frame__tilt">
                <div class="frame__molding">
                    <div class="frame__mat">
                        <div class="frame__canvas">
                            <svg class="glyph" viewBox="0 0 460 190" role="img" aria-label="404">
                                <text class="glyph__text" x="230" y="152" text-anchor="middle">404</text>
                            </svg>
                            <span class="frame__hint">{{ __('site.e404_hint') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="plaque">
            <strong>{{ __('site.e404_plaque_title') }}</strong>
            <span>{{ __('site.e404_plaque_meta') }}</span>
        </div>

        <div class="gallery__copy">
            <p class="eyebrow">{{ __('site.e404_eyebrow') }}</p>
            <h1>{{ __('site.e404_title') }}</h1>
            <p>{{ __('site.e404_text') }}</p>
        </div>

        <div class="gallery__actions">
            <a class="btn btn--primary" href="{{ route('home') }}">{{ __('site.e404_home') }}</a>
            <a class="btn btn--outline" href="{{ route('catalog.plastic') }}">{{ __('site.cta_plastic') }}</a>
            <a class="btn btn--outline" href="{{ route('catalog.hardware') }}">{{ __('site.cta_hardware') }}</a>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const stage = document.querySelector('[data-gallery]');
    if (!stage) return;

    let frame = null;

    stage.addEventListener('pointermove', (event) => {
        if (frame) return;
        frame = requestAnimationFrame(() => {
            frame = null;
            const rect = stage.getBoundingClientRect();
            stage.style.setProperty('--px', (event.clientX - rect.width / 2).toFixed(1));
            stage.style.setProperty('--py', (event.clientY - rect.height / 2).toFixed(1));
        });
    });

    stage.addEventListener('pointerleave', () => {
        stage.style.setProperty('--px', '0');
        stage.style.setProperty('--py', '0');
    });
})();
</script>
</body>
</html>
