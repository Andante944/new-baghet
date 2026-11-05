<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'plastic'],
            ['name_ru' => 'Пластиковый багет', 'name_ro' => 'Baghetă plastic'],
        );

        return Product::query()->create($attributes + [
            'category_id' => $category->id,
            'sku' => '100-01-01',
            'slug' => '100-01-01-admin',
            'name_ru' => 'Тестовый багет 100-01-01',
            'name_ro' => 'Baghetă test 100-01-01',
            'price' => 12.5,
            'stock' => 4,
            'is_active' => true,
        ]);
    }

    public function test_guest_and_client_cannot_open_products_admin(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $this->actingAs($client)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_admin_sees_sku_and_products_without_image(): void
    {
        $admin = $this->admin();
        $this->product(['sku' => '200-02-02', 'name_ru' => 'Багет без фото']);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('200-02-02')
            ->assertSee('Багет без фото')
            ->assertSee(__('site.admin_products_missing_image'));

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['image' => 'missing']))
            ->assertOk()
            ->assertSee('200-02-02');
    }

    public function test_admin_can_replace_image_with_upload(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/old.jpg', 'old');
        $admin = $this->admin();
        $product = $this->product(['image_path' => 'products/old.jpg']);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'sku' => $product->sku,
                'name_ru' => $product->name_ru,
                'name_ro' => $product->name_ro,
                'image' => UploadedFile::fake()->image('new.jpg', 400, 300),
            ])
            ->assertRedirect(route('admin.products.edit', $product));

        $product->refresh();
        $this->assertStringStartsWith('products/', (string) $product->image_path);
        $this->assertNotSame('products/old.jpg', $product->image_path);
        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_can_set_image_url_and_remove_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $product = $this->product();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'sku' => $product->sku,
                'name_ru' => $product->name_ru,
                'image_url' => 'https://example.com/frame.jpg',
            ])
            ->assertRedirect();

        $this->assertSame('https://example.com/frame.jpg', $product->fresh()->image_path);

        $this->actingAs($admin)
            ->delete(route('admin.products.image.destroy', $product))
            ->assertRedirect(route('admin.products.edit', $product));

        $this->assertNull($product->fresh()->image_path);
    }

    public function test_admin_can_fix_sku(): void
    {
        $admin = $this->admin();
        $product = $this->product(['sku' => 'WRONG-SKU']);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'sku' => '100-01-99',
                'name_ru' => $product->name_ru,
            ])
            ->assertRedirect();

        $this->assertSame('100-01-99', $product->fresh()->sku);
    }

    public function test_product_page_shows_admin_edit_link(): void
    {
        $admin = $this->admin();
        $product = $this->product();

        $this->actingAs($admin)
            ->get(route('catalog.show', $product->slug))
            ->assertOk()
            ->assertSee(route('admin.products.edit', $product), false);
    }
}
