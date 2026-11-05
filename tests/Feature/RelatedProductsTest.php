<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatedProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_related_products_stay_within_the_sku_width_family(): void
    {
        $category = $this->category();

        $current = $this->product($category, '15-01-01', 'current-15-01-01');
        foreach (['15-02-01', '15-02-02', '15-02-03', '15-02-04'] as $index => $sku) {
            $this->product($category, $sku, 'family-'.$index);
        }
        $this->product($category, '20-01-01', 'other-width');

        $related = $this->get(route('catalog.show', $current->slug))
            ->assertOk()
            ->viewData('related');

        $skus = $related->pluck('sku')->all();

        $this->assertCount(4, $skus);
        $this->assertSame(['15-02-01', '15-02-02', '15-02-03', '15-02-04'], $skus);
        $this->assertNotContains('20-01-01', $skus);
    }

    public function test_same_series_is_preferred_before_wider_width_group(): void
    {
        $category = $this->category();
        $current = $this->product($category, '20-02-01', 'current-20-02-01');
        $this->product($category, '20-02-02', 'same-series');
        $this->product($category, '20-01-01', 'other-series');
        $this->product($category, '20-03-01', 'other-series-2');
        $this->product($category, '25-01-01', 'other-width');

        $related = $this->get(route('catalog.show', $current->slug))
            ->assertOk()
            ->viewData('related');

        $this->assertSame('20-02-02', $related->first()->sku);
        $this->assertSame(
            ['20-02-02', '20-01-01', '20-03-01'],
            $related->pluck('sku')->all()
        );
    }

    private function category(): Category
    {
        return Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);
    }

    private function product(Category $category, string $sku, string $slug): Product
    {
        return Product::query()->create([
            'category_id' => $category->id,
            'sku' => $sku,
            'slug' => $slug,
            'name_ru' => 'Багет '.$sku,
            'price' => 1,
            'stock' => 10,
            'is_active' => true,
        ]);
    }
}
