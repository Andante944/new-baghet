{{--
    Новогоднее украшение логотипа: ёлочный шар свисает с рамки + снежинки.
    Показывается автоматически с 15 ноября по 20 января включительно,
    после 20 января возвращается стандартный логотип.
--}}
@php
    $decorToday = now('Europe/Chisinau');
    $showNewYearDecor = $decorToday->month === 12
        || ($decorToday->month === 1 && $decorToday->day <= 20)
        || ($decorToday->month === 11 && $decorToday->day >= 15);
@endphp
@if ($showNewYearDecor)
    <span class="logo__decor" aria-hidden="true">
        <svg viewBox="0 0 648 248" focusable="false" role="presentation">
            <g class="logo__ornament" transform="translate(620,80)">
                <circle cx="0" cy="-68" r="7.5" fill="none" stroke="#b08d57" stroke-width="4.5"/>
                <rect x="-17" y="-64" width="34" height="20" rx="7" fill="#b08d57"/>
                <rect x="-17" y="-51" width="34" height="7" rx="3.5" fill="#8a6c3e" opacity=".55"/>
                <circle cx="0" cy="0" r="52" fill="#cf1f2e"/>
                <path d="M-52 0A52 52 0 0 0 52 0A52 52 0 0 1-52 0Z" fill="#8f0f1a" opacity=".4"/>
                <ellipse cx="-17" cy="-19" rx="15" ry="9" fill="#ffffff" opacity=".28" transform="rotate(-32 -17 -19)"/>
                <path d="M-37 9C-28 32 -5 42 18 37" stroke="#e8565f" stroke-width="5" fill="none" opacity=".4" stroke-linecap="round"/>
            </g>
            <g fill="none" stroke="#b08d57" stroke-linecap="round" stroke-linejoin="round">
                <g transform="translate(300,-38) scale(0.5)" stroke-width="7">
                    <path d="M32 4L32 34M32 13L25.5 19.5M32 13L38.5 19.5M32 23L27 28M32 23L37 28M56.25 18L30.27 33M48.45 22.5L39.58 20.12M48.45 22.5L46.08 31.38M39.79 27.5L32.96 25.67M39.79 27.5L37.96 34.33M56.25 46L30.27 31M48.45 41.5L46.08 32.62M48.45 41.5L39.58 43.88M39.79 36.5L37.96 29.67M39.79 36.5L32.96 38.33M32 60L32 30M32 51L38.5 44.5M32 51L25.5 44.5M32 41L37 36M32 41L27 36M7.75 46L33.73 31M15.55 41.5L24.42 43.88M15.55 41.5L17.92 32.62M24.21 36.5L31.04 38.33M24.21 36.5L26.04 29.67M7.75 18L33.73 33M15.55 22.5L17.92 31.38M15.55 22.5L24.42 20.12M24.21 27.5L26.04 34.33M24.21 27.5L31.04 25.67"/>
                </g>
                <g transform="translate(120,-30) scale(0.42)" stroke-width="8">
                    <path d="M32 4L32 34M32 13L25.5 19.5M32 13L38.5 19.5M32 23L27 28M32 23L37 28M56.25 18L30.27 33M48.45 22.5L39.58 20.12M48.45 22.5L46.08 31.38M39.79 27.5L32.96 25.67M39.79 27.5L37.96 34.33M56.25 46L30.27 31M48.45 41.5L46.08 32.62M48.45 41.5L39.58 43.88M39.79 36.5L37.96 29.67M39.79 36.5L32.96 38.33M32 60L32 30M32 51L38.5 44.5M32 51L25.5 44.5M32 41L37 36M32 41L27 36M7.75 46L33.73 31M15.55 41.5L24.42 43.88M15.55 41.5L17.92 32.62M24.21 36.5L31.04 38.33M24.21 36.5L26.04 29.67M7.75 18L33.73 33M15.55 22.5L17.92 31.38M15.55 22.5L24.42 20.12M24.21 27.5L26.04 34.33M24.21 27.5L31.04 25.67"/>
                </g>
            </g>
        </svg>
    </span>
@endif
