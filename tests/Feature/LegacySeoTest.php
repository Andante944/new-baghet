<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacySeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_prestashop_product_url_redirects_to_current_product(): void
    {
        $category = $this->category();
        $product = $this->product($category, [
            'sku' => '15-01-01',
            'slug' => 'legacy-15-01-01',
            'prestashop_id' => 41,
        ]);

        $this->get('/index.php?controller=product&id_product=41')
            ->assertStatus(301)->assertRedirect(route('catalog.show', $product->slug));

        $this->get('/ro/index.php?controller=product&id_product=41')
            ->assertStatus(301)->assertRedirect(route('catalog.show', $product->slug));
    }

    public function test_legacy_inactive_product_goes_to_its_category(): void
    {
        $category = $this->category();
        $this->product($category, [
            'sku' => '15-01-02',
            'slug' => 'legacy-inactive',
            'prestashop_id' => 42,
            'is_active' => false,
        ]);

        $this->get('/index.php?controller=product&id_product=42')
            ->assertStatus(301)->assertRedirect(route('catalog.plastic'));
    }

    public function test_inactive_product_slug_redirects_to_category_instead_of_404(): void
    {
        $category = $this->category();
        $this->product($category, [
            'sku' => '15-01-03',
            'slug' => 'inactive-product',
            'is_active' => false,
        ]);

        $this->get(route('catalog.show', 'inactive-product'))
            ->assertStatus(301)->assertRedirect(route('catalog.plastic'));
    }

    public function test_duplicate_sku_redirects_to_crm_bound_card(): void
    {
        $category = $this->category();
        $this->product($category, [
            'sku' => '20-02-04',
            'slug' => 'duplicate-without-crm',
            'crm_product_id' => null,
        ]);
        $primary = $this->product($category, [
            'sku' => '20-02-04',
            'slug' => 'duplicate-with-crm',
            'crm_product_id' => 37,
        ]);

        $this->get(route('catalog.show', 'duplicate-without-crm'))
            ->assertStatus(301)->assertRedirect(route('catalog.show', $primary->slug));
    }

    public function test_sitemap_contains_only_the_canonical_duplicate(): void
    {
        $category = $this->category();
        $this->product($category, [
            'sku' => '35-03-11',
            'slug' => 'duplicate-35-03-11-old',
            'crm_product_id' => null,
        ]);
        $primary = $this->product($category, [
            'sku' => '35-03-11',
            'slug' => 'duplicate-35-03-11-crm',
            'crm_product_id' => 152,
        ]);

        $xml = (string) $this->get(route('sitemap'))->assertOk()->getContent();

        $this->assertStringContainsString(route('catalog.show', $primary->slug), $xml);
        $this->assertStringNotContainsString(route('catalog.show', 'duplicate-35-03-11-old'), $xml);
    }

    public function test_legacy_locale_product_urls_redirect_to_the_new_card(): void
    {
        $category = $this->category();
        $product = $this->product($category, [
            'sku' => '15-02-02',
            'slug' => 'legacy-localized-product',
            'prestashop_id' => 41,
        ]);

        $this->get('/ru/plastikovyj-baget/41-plastikovyj-baget-15-02-02.html')
            ->assertStatus(301)->assertRedirect(route('catalog.show', $product->slug));

        $this->get('/ro/plastikovyj-baget/41-plastikovyj-baget-15-02-02.html')
            ->assertStatus(301)->assertRedirect(route('catalog.show', $product->slug));
    }

    public function test_legacy_locale_pages_redirect_to_new_sections(): void
    {
        $category = $this->category();
        $this->product($category, [
            'sku' => '15-01-04',
            'slug' => 'legacy-inactive-localized',
            'prestashop_id' => 42,
            'is_active' => false,
        ]);

        $this->get('/ru/plastikovyj-baget/42-plastikovyj-baget-15-01-04.html')
            ->assertStatus(301)->assertRedirect(route('catalog.plastic'));

        $this->get('/ru/contact')->assertStatus(301)->assertRedirect(route('contacts'));
        $this->get('/ru/content/7-optovym-pokupatelyam')->assertStatus(301)->assertRedirect(route('wholesale'));
        $this->get('/ru/')->assertStatus(301)->assertRedirect(route('home'));
    }

    private function category(): Category
    {
        return Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(Category $category, array $attributes): Product
    {
        return Product::query()->create($attributes + [
            'category_id' => $category->id,
            'sku' => '100-01-01',
            'slug' => 'test-product',
            'name_ru' => 'Тестовый багет',
            'price' => 5,
            'stock' => 1,
            'is_active' => true,
        ]);
    }
}
