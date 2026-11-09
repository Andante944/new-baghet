<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Old PrestaShop URLs (`index.php?controller=product&id_product=…`) are still
 * indexed by search engines. Send them to the matching showcase page with a
 * permanent redirect instead of letting them fall through to a soft 404/home.
 */
class LegacyPrestashopRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        $controller = strtolower(trim((string) $request->query('controller', '')));

        if ($controller === 'product') {
            $prestashopId = (int) $request->query('id_product', 0);
            $product = $prestashopId > 0
                ? Product::query()->with('category')->where('prestashop_id', $prestashopId)->first()
                : null;

            if ($product === null) {
                return redirect()->route('home', [], 301);
            }

            if ($product->is_active) {
                return redirect()->route('catalog.show', $product->slug, 301);
            }

            return redirect()->route(
                $product->category?->slug === 'hardware' ? 'catalog.hardware' : 'catalog.plastic',
                [],
                301,
            );
        }

        if ($controller === 'category') {
            $prestashopId = (int) $request->query('id_category', 0);
            $category = $prestashopId > 0
                ? Category::query()->where('prestashop_id', $prestashopId)->first()
                : null;

            return redirect()->route(
                $category?->slug === 'hardware' ? 'catalog.hardware' : ($category?->slug === 'plastic' ? 'catalog.plastic' : 'home'),
                [],
                301,
            );
        }

        if ($controller !== '') {
            return redirect()->route('home', [], 301);
        }

        $path = trim((string) $request->path(), '/');
        if ($path === '') {
            return $next($request);
        }

        // Old PrestaShop friendly URLs: /ru/plastikovyj-baget/41-....html
        // and /ro|en/... locale prefixes that no longer exist on the new site.
        $localized = preg_match('#^(ru|ro|en)(?:/|$)#i', $path, $localeMatch) === 1;
        if ($localized) {
            $path = trim(substr($path, strlen($localeMatch[1])), '/');
        }

        if ($localized === false && ! preg_match('#(?:^|/)\d+-[^/]+\.html$#i', $path)) {
            return $next($request);
        }

        if (preg_match('#(?:^|/)(\d+)-[^/]+\.html$#i', $path, $matches) === 1) {
            $product = Product::query()
                ->with('category')
                ->where('prestashop_id', (int) $matches[1])
                ->first();

            if ($product !== null) {
                return redirect()->route(
                    $product->is_active
                        ? 'catalog.show'
                        : ($product->category?->slug === 'hardware' ? 'catalog.hardware' : 'catalog.plastic'),
                    $product->is_active ? [$product->slug] : [],
                    301,
                );
            }
        }

        $normalized = mb_strtolower($path);
        $route = match (true) {
            str_contains($normalized, 'optovym') => 'wholesale',
            str_starts_with($normalized, 'contact') => 'contacts',
            str_contains($normalized, 'plastikovyj-baget'),
            preg_match('#^11-#', $normalized) === 1 => 'catalog.plastic',
            str_contains($normalized, 'furnitura'),
            preg_match('#^16-#', $normalized) === 1 => 'catalog.hardware',
            default => 'home',
        };

        return redirect()->route($route, [], 301);
    }
}
