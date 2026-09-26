<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    public static function isPrivateRoute(?string $routeName = null): bool
    {
        $routeName ??= request()->route()?->getName();

        if (! $routeName) {
            return false;
        }

        return Str::startsWith($routeName, [
            'login',
            'dashboard',
            'cart.',
            'orders.',
            'admin.',
            'catalog.search',
        ]);
    }

    public static function robots(?string $override = null): string
    {
        if ($override) {
            return $override;
        }

        if (self::isPrivateRoute()) {
            return 'noindex, nofollow';
        }

        // Filtered catalog pages: keep crawlable but prefer clean canonical.
        if (request()->filled(['width', 'color', 'type'])) {
            return 'noindex, follow';
        }

        return 'index, follow';
    }

    public static function absolute(string $pathOrUrl): string
    {
        if (Str::startsWith($pathOrUrl, ['http://', 'https://'])) {
            return $pathOrUrl;
        }

        return url($pathOrUrl);
    }

    public static function defaultImage(): string
    {
        return asset('images/hero-molding.jpg');
    }

    public static function langUrl(string $locale, ?string $url = null): string
    {
        $url ??= url()->current();
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);
        $query['lang'] = $locale;

        $base = ($parts['scheme'] ?? request()->getScheme()).'://'
            .($parts['host'] ?? request()->getHost())
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .($parts['path'] ?? '/');

        return $base.'?'.http_build_query($query);
    }
}
