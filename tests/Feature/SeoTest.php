<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_has_core_seo_tags_and_faq_schema(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('hreflang="ru"', false)
            ->assertSee('hreflang="ro"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('FAQPage', false)
            ->assertSee('LocalBusiness', false);
    }

    public function test_catalog_filter_is_noindex_with_clean_canonical(): void
    {
        Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);

        $response = $this->get(route('catalog.plastic', ['width' => ['1-3']]));

        $response->assertOk()
            ->assertSee('noindex, follow', false)
            ->assertSee('rel="canonical" href="'.route('catalog.plastic').'"', false);
    }

    public function test_product_page_has_product_schema(): void
    {
        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);
        Product::query()->create([
            'category_id' => $category->id,
            'sku' => '100-01-01',
            'slug' => '100-01-01-seo',
            'name_ru' => 'Пластиковый багет: 100-01-01',
            'price' => 12.5,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->get(route('catalog.show', '100-01-01-seo'))
            ->assertOk()
            ->assertSee('"@type": "Product"', false)
            ->assertSee('"sku": "100-01-01"', false)
            ->assertSee('BreadcrumbList', false);
    }

    public function test_private_pages_are_noindex(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('noindex, nofollow', false);
    }

    public function test_robots_and_sitemap_are_available(): void
    {
        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);
        Product::query()->create([
            'category_id' => $category->id,
            'sku' => '200-01-01',
            'slug' => '200-01-01-map',
            'name_ru' => 'Пластиковый багет: 200-01-01',
            'price' => 8,
            'stock' => 1,
            'is_active' => true,
        ]);

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap:');

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'), false)
            ->assertSee(route('catalog.show', '200-01-01-map'), false);
    }

    public function test_search_api_sends_noindex_header(): void
    {
        $this->getJson(route('catalog.search', ['q' => 'ab']))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
