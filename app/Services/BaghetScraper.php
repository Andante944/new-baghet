<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class BaghetScraper
{
    private const BASE = 'https://baghet.md/index.php';

    private const LANG_RU = 3;

    private const LANG_RO = 4;

    /** @var array<string, int> */
    private array $categories = [
        'plastic' => 11,
        'hardware' => 16,
    ];

    /** @var array<string, string> */
    private array $colorMap = [
        'белый' => 'white',
        'белая' => 'white',
        'золотой' => 'gold',
        'золотая' => 'gold',
        'серебряный' => 'silver',
        'серебряная' => 'silver',
        'коричневый' => 'brown',
        'коричневая' => 'brown',
        'чёрный' => 'black',
        'черный' => 'black',
        'чёрная' => 'black',
        'черная' => 'black',
        'красный' => 'red',
        'красная' => 'red',
        'синий' => 'blue',
        'синяя' => 'blue',
        'зелёный' => 'green',
        'зеленый' => 'green',
        'жёлтый' => 'yellow',
        'желтый' => 'yellow',
        'alb' => 'white',
        'auriu' => 'gold',
        'argintiu' => 'silver',
        'maro' => 'brown',
        'negru' => 'black',
        'roșu' => 'red',
        'rosu' => 'red',
        'albastru' => 'blue',
    ];

    public function ensureCategories(): void
    {
        Category::query()->updateOrCreate(
            ['slug' => 'plastic'],
            [
                'name_ru' => 'Пластиковый багет',
                'name_ro' => 'Baghetă plastic',
                'prestashop_id' => $this->categories['plastic'],
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'hardware'],
            [
                'name_ru' => 'Фурнитура',
                'name_ro' => 'Feronerie',
                'prestashop_id' => $this->categories['hardware'],
            ]
        );
    }

    /**
     * @return array{imported: int, updated: int, failed: int}
     */
    public function scrape(?string $only = null, ?int $limit = null, bool $withImages = true): array
    {
        $this->ensureCategories();

        $stats = ['imported' => 0, 'updated' => 0, 'failed' => 0];
        $targets = $only ? [$only => $this->categories[$only]] : $this->categories;

        foreach ($targets as $slug => $categoryId) {
            $category = Category::query()->where('slug', $slug)->firstOrFail();
            $products = $this->collectProducts($categoryId);

            if ($limit !== null) {
                $products = array_slice($products, 0, $limit);
            }

            foreach ($products as $item) {
                try {
                    $created = $this->importProduct($item, $category, $withImages);
                    $stats[$created ? 'imported' : 'updated']++;
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    Log::warning('Baghet scrape failed', [
                        'product_id' => $item['id'],
                        'error' => $e->getMessage(),
                    ]);
                }

                usleep(150000);
            }
        }

        return $stats;
    }

    /**
     * @return list<array{id: int, url: string}>
     */
    public function collectProducts(int $categoryId): array
    {
        $items = [];
        $page = 1;
        $maxPage = 1;

        do {
            $html = $this->fetch(self::BASE, [
                'id_category' => $categoryId,
                'controller' => 'category',
                'id_lang' => self::LANG_RU,
                'page' => $page,
            ]);

            $crawler = new Crawler($html);
            $crawler->filter('article.product-miniature')->each(function (Crawler $node) use (&$items) {
                $id = (int) $node->attr('data-id-product');
                $href = $node->filter('a.product-thumbnail, h2.product-title a')->first()->attr('href');
                if ($id > 0 && $href) {
                    $items[$id] = [
                        'id' => $id,
                        'url' => html_entity_decode($href),
                    ];
                }
            });

            $crawler->filter('nav.pagination a.js-search-link')->each(function (Crawler $node) use (&$maxPage) {
                $href = $node->attr('href') ?? '';
                if (preg_match('/[?&]page=(\d+)/', html_entity_decode($href), $m)) {
                    $maxPage = max($maxPage, (int) $m[1]);
                }
            });

            $page++;
        } while ($page <= $maxPage);

        return array_values($items);
    }

    /**
     * @param  array{id: int, url: string}  $item
     */
    public function importProduct(array $item, Category $category, bool $withImages = true): bool
    {
        $prestashopId = $item['id'];
        $ruUrl = $this->withLang($item['url'], self::LANG_RU);
        $roUrl = $this->withLang($item['url'], self::LANG_RO);

        $ruHtml = $this->fetchUrl($ruUrl);
        $ru = $this->parseProductPage($ruHtml);

        try {
            $roHtml = $this->fetchUrl($roUrl);
            $ro = $this->parseProductPage($roHtml);
        } catch (\Throwable $e) {
            Log::info('RO product page unavailable, using RU', ['product_id' => $prestashopId]);
            $ro = $ru;
        }

        $sku = $ru['sku'] ?: (string) $prestashopId;
        $slug = Str::slug($sku.'-'.$prestashopId);

        $existing = Product::query()->where('prestashop_id', $prestashopId)->first();
        $imagePath = $existing?->image_path;

        if ($withImages && $ru['image']) {
            $downloaded = $this->downloadImage($ru['image'], $prestashopId);
            if ($downloaded) {
                $imagePath = $downloaded;
            }
        }

        $payload = [
            'category_id' => $category->id,
            'sku' => $sku,
            'slug' => $slug,
            'name_ru' => $ru['name'] ?: 'Товар '.$sku,
            'name_ro' => $ro['name'] ?: $ru['name'],
            'description_ru' => $ru['description'],
            'description_ro' => $ro['description'] ?: $ru['description'],
            'price' => $ru['price'],
            'stock' => $ru['stock'],
            'width' => $ru['width'],
            'color' => $ru['color'],
            'type' => $category->slug === 'hardware' ? ($ru['type'] ?? 'other') : null,
            'image_path' => $imagePath,
            'source_url' => $ruUrl,
            'is_active' => true,
        ];

        // CRM owns name + sale price once bound; keep scraped images/stock/attrs.
        if ($existing?->crm_product_id) {
            unset($payload['name_ru'], $payload['name_ro'], $payload['price']);
        }

        if ($existing) {
            $existing->update($payload);

            return false;
        }

        Product::query()->create(array_merge($payload, [
            'prestashop_id' => $prestashopId,
        ]));

        return true;
    }

    /**
     * @return array{name: ?string, sku: ?string, price: float, stock: int, description: ?string, image: ?string, width: ?string, color: ?string, type: ?string}
     */
    public function parseProductPage(string $html): array
    {
        $crawler = new Crawler($html);

        $name = null;
        if ($crawler->filter('h1')->count()) {
            $name = trim($crawler->filter('h1')->first()->text(''));
        }

        $sku = null;
        if ($crawler->filter('.product-reference span')->count()) {
            $sku = trim($crawler->filter('.product-reference span')->last()->text(''));
        }
        if (! $sku && preg_match('/:\s*([0-9]{2}-[0-9]{2}-[0-9]{2})\s*$/u', (string) $name, $m)) {
            $sku = $m[1];
        }

        $price = 0.0;
        if ($crawler->filter('[itemprop="price"]')->count()) {
            $price = (float) $crawler->filter('[itemprop="price"]')->attr('content');
        }

        $description = null;
        if ($crawler->filter('#description')->count()) {
            $description = trim($crawler->filter('#description')->text(''));
        } elseif ($crawler->filter('.product-description')->count()) {
            $description = trim($crawler->filter('.product-description')->first()->text(''));
        }

        $image = null;
        if ($crawler->filter('[data-image-large-src]')->count()) {
            $image = $crawler->filter('[data-image-large-src]')->first()->attr('data-image-large-src');
        } elseif ($crawler->filter('.product-cover img')->count()) {
            $image = $crawler->filter('.product-cover img')->first()->attr('src');
        }

        $features = [];
        $crawler->filter('.product-features dl')->each(function (Crawler $dl) use (&$features) {
            $dts = $dl->filter('dt');
            $dds = $dl->filter('dd');
            foreach ($dts as $i => $dt) {
                $key = mb_strtolower(trim($dt->textContent));
                $val = trim($dds->eq($i)->text(''));
                $features[$key] = $val;
            }
        });

        $width = null;
        $color = null;
        foreach ($features as $key => $value) {
            if (str_contains($key, 'размер') || str_contains($key, 'ширин') || str_contains($key, 'lățime') || str_contains($key, 'latime')) {
                $width = $this->normalizeWidth($value);
            }
            if (str_contains($key, 'цвет') || str_contains($key, 'culoare')) {
                $color = $this->normalizeColor($value);
            }
        }

        $stock = 1;
        if (preg_match('/"quantity"\s*:\s*(\d+)/', $html, $m)) {
            $stock = (int) $m[1];
        } elseif ($crawler->filter('.product-unavailable')->count()) {
            $stock = 0;
        }

        $type = $this->guessHardwareType($name.' '.$description);

        return [
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'stock' => $stock,
            'description' => $description ?: null,
            'image' => $image,
            'width' => $width,
            'color' => $color,
            'type' => $type,
        ];
    }

    private function normalizeWidth(string $value): string
    {
        $v = mb_strtolower(str_replace([' ', '–'], ['', '-'], $value));
        if (preg_match('/1-?3/', $v)) {
            return '1-3';
        }
        if (preg_match('/4-?5/', $v)) {
            return '4-5';
        }
        if (preg_match('/6-?7/', $v)) {
            return '6-7';
        }
        if (preg_match('/8-?10/', $v)) {
            return '8-10';
        }

        return $value;
    }

    private function normalizeColor(string $value): ?string
    {
        $key = mb_strtolower(trim($value));

        return $this->colorMap[$key] ?? Str::slug($key);
    }

    private function guessHardwareType(?string $text): string
    {
        $t = mb_strtolower((string) $text);
        if (str_contains($t, 'скоб') || str_contains($t, 'caps') || str_contains($t, 'v-')) {
            return 'staples';
        }
        if (str_contains($t, 'подвес') || str_contains($t, 'agăț') || str_contains($t, 'agat')) {
            return 'hangers';
        }
        if (str_contains($t, 'угол') || str_contains($t, 'colț') || str_contains($t, 'colt')) {
            return 'corners';
        }

        return 'other';
    }

    private function downloadImage(string $url, int $productId): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => $this->caBundle()])
                ->withHeaders(['User-Agent' => 'BaghetScraper/1.0'])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
            $path = "products/{$productId}.{$ext}";
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable $e) {
            Log::warning('Image download failed', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private function withLang(string $url, int $langId): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);
        $query['id_lang'] = $langId;

        $base = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? 'baghet.md').($parts['path'] ?? '/index.php');

        return $base.'?'.http_build_query($query);
    }

    /**
     * @param  array<string, scalar>  $query
     */
    private function fetch(string $url, array $query = []): string
    {
        return $this->fetchUrl($url.(str_contains($url, '?') ? '&' : '?').http_build_query($query));
    }

    private function fetchUrl(string $url): string
    {
        $response = Http::timeout(45)
            ->withOptions(['verify' => $this->caBundle()])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; BaghetScraper/1.0)',
                'Accept-Language' => 'ru-RU,ru;q=0.9',
            ])
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status().' for '.$url);
        }

        $body = $response->body();
        if (str_contains($body, 'This page has moved')) {
            throw new \RuntimeException('Redirect/debug page for '.$url);
        }

        return $body;
    }

    private function caBundle(): string|bool
    {
        $phpIniCa = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');
        if ($phpIniCa && is_file($phpIniCa)) {
            return $phpIniCa;
        }

        $fallback = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'cacert.pem';

        return is_file($fallback) ? $fallback : true;
    }
}
