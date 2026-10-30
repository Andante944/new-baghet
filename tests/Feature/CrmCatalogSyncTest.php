<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\BaghetScraper;
use App\Services\CrmCatalogSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrmCatalogSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_binds_name_and_sale_price_by_sku(): void
    {
        config([
            'services.crm.catalog_url' => 'https://crm.example/api/bagheta/catalog',
            'services.crm.catalog_token' => 'secret-token',
        ]);

        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => '100-01-01',
            'slug' => '100-01-01',
            'name_ru' => 'Old scraped name',
            'price' => 5.00,
            'stock' => 10,
            'image_path' => 'products/1.jpg',
            'is_active' => true,
        ]);

        Http::fake([
            'crm.example/*' => Http::response([
                'data' => [
                    [
                        'id' => 42,
                        'sku' => '100-01-01',
                        'name' => 'CRM bagheta name',
                        'sale_price' => 12.50,
                        'sale_price_per_meter' => 4.31,
                    ],
                ],
            ]),
        ]);

        $stats = app(CrmCatalogSync::class)->sync();

        $this->assertSame(1, $stats['matched']);
        $this->assertSame(1, $stats['updated']);

        $product->refresh();
        $this->assertSame(42, $product->crm_product_id);
        $this->assertSame('CRM bagheta name', $product->name_ru);
        // Витрина торгует за метр: берём sale_price_per_meter, а не цену палки.
        $this->assertSame('4.3100', $product->price);
        $this->assertSame('products/1.jpg', $product->image_path);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer secret-token')
                && $request->url() === 'https://crm.example/api/bagheta/catalog';
        });
    }

    public function test_scrape_does_not_overwrite_crm_bound_name_and_price(): void
    {
        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'prestashop_id' => 99,
            'sku' => '100-01-01',
            'slug' => '100-01-01-99',
            'name_ru' => 'CRM name',
            'name_ro' => 'CRM name RO',
            'price' => 15.00,
            'stock' => 3,
            'crm_product_id' => 42,
            'image_path' => 'products/99.jpg',
            'is_active' => true,
        ]);

        $html = <<<'HTML'
        <html><body>
          <h1>Scraped overwrite attempt</h1>
          <div class="product-reference">Reference <span>100-01-01</span></div>
          <span itemprop="price" content="1.00"></span>
          <div id="description">New description</div>
          <script type="application/ld+json">{"quantity":99}</script>
        </body></html>
        HTML;

        Http::fake([
            'baghet.md/*' => Http::response($html),
        ]);

        app(BaghetScraper::class)->importProduct([
            'id' => 99,
            'url' => 'https://baghet.md/index.php?id_product=99&controller=product',
        ], $category, false);

        $product->refresh();
        $this->assertSame('CRM name', $product->name_ru);
        $this->assertSame('CRM name RO', $product->name_ro);
        $this->assertSame('15.0000', $product->price);
        $this->assertSame(42, $product->crm_product_id);
        $this->assertSame(99, $product->stock);
        $this->assertSame('New description', $product->description_ru);
    }

    public function test_artisan_command_reports_stats(): void
    {
        config([
            'services.crm.catalog_url' => 'https://crm.example/api/bagheta/catalog',
            'services.crm.catalog_token' => 'token',
        ]);

        Http::fake([
            'crm.example/*' => Http::response(['data' => []]),
        ]);

        $this->artisan('baghet:sync-crm-catalog')
            ->assertSuccessful();
    }
}
