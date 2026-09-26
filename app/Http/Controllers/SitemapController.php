<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = [];

            $urls[] = $this->url(route('home'), now(), 'daily', '1.0');
            $urls[] = $this->url(route('catalog.plastic'), now(), 'daily', '0.9');
            $urls[] = $this->url(route('catalog.hardware'), now(), 'daily', '0.9');
            $urls[] = $this->url(route('contacts'), now(), 'monthly', '0.7');

            Product::query()
                ->active()
                ->orderBy('id')
                ->get(['slug', 'updated_at'])
                ->each(function (Product $product) use (&$urls) {
                    $urls[] = $this->url(
                        route('catalog.show', $product->slug),
                        $product->updated_at,
                        'weekly',
                        '0.8'
                    );
                });

            return '<?xml version="1.0" encoding="UTF-8"?>'
                ."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
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

    private function url(string $loc, $lastmod = null, string $changefreq = 'weekly', string $priority = '0.5'): string
    {
        $lastmodXml = $lastmod
            ? '<lastmod>'.optional($lastmod)->toAtomString().'</lastmod>'
            : '';

        return '<url>'
            .'<loc>'.e($loc).'</loc>'
            .$lastmodXml
            .'<changefreq>'.$changefreq.'</changefreq>'
            .'<priority>'.$priority.'</priority>'
            .'</url>';
    }
}
