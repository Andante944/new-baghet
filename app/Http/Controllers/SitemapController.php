<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = [];
            $hasImages = false;

            $urls[] = $this->url(rtrim(route('home'), '/').'/', now(), 'daily', '1.0');
            $urls[] = $this->url(route('catalog.plastic'), now(), 'daily', '0.9');
            $urls[] = $this->url(route('catalog.hardware'), now(), 'daily', '0.9');
            $urls[] = $this->url(route('wholesale'), now(), 'monthly', '0.8');
            $urls[] = $this->url(route('contacts'), now(), 'monthly', '0.7');

            $products = Product::query()
                ->active()
                ->orderByRaw('CASE WHEN crm_product_id IS NULL THEN 1 ELSE 0 END')
                ->orderByRaw("CASE WHEN image_path IS NULL OR image_path = '' THEN 1 ELSE 0 END")
                ->orderBy('id')
                ->get(['id', 'sku', 'name_ru', 'slug', 'updated_at', 'image_path', 'crm_product_id']);

            // One URL per SKU: duplicate imports canonicalise/redirect to the
            // CRM-bound card, so only that card belongs in the sitemap.
            $seen = [];
            $canonicalProducts = [];
            foreach ($products as $product) {
                $sku = trim((string) $product->sku);
                $key = $sku !== '' ? 'sku:'.mb_strtolower($sku) : 'name:'.mb_strtolower((string) $product->name_ru);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $canonicalProducts[] = $product;
            }

            foreach (collect($canonicalProducts)->sortBy('sku') as $product) {
                $image = $product->imageUrl();
                if ($image !== null) {
                    $hasImages = true;
                }

                $urls[] = $this->url(
                    route('catalog.show', $product->slug),
                    $product->updated_at,
                    'weekly',
                    '0.8',
                    $image,
                );
            }

            $namespace = $hasImages
                ? ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'
                : '';

            return '<?xml version="1.0" encoding="UTF-8"?>'
                ."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.$namespace.'>'
                ."\n".implode("\n", $urls)
                ."\n".'</urlset>';
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /login',
            'Disallow: /cabinet',
            'Disallow: /cart',
            'Disallow: /orders',
            'Disallow: /admin',
            'Disallow: /search',
            'Disallow: /locale/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function url(
        string $loc,
        $lastmod = null,
        string $changefreq = 'weekly',
        string $priority = '0.5',
        ?string $image = null,
    ): string {
        $lastmodXml = $lastmod
            ? '<lastmod>'.optional($lastmod)->toAtomString().'</lastmod>'
            : '';
        $imageXml = $image
            ? '<image:image><image:loc>'.e(Seo::absolute($image)).'</image:loc></image:image>'
            : '';

        return '<url>'
            .'<loc>'.e($loc).'</loc>'
            .$lastmodXml
            .'<changefreq>'.$changefreq.'</changefreq>'
            .'<priority>'.$priority.'</priority>'
            .$imageXml
            .'</url>';
    }
}
