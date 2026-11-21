<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ClientDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'services.crm.catalog_url' => 'https://crm.example/api/bagheta/catalog',
            'services.crm.catalog_token' => 'secret-token',
        ]);
    }

    private function client(int $companyId = 46): User
    {
        return User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'crm_company_id' => $companyId,
        ]);
    }

    public function test_guest_and_user_without_company_have_no_discount(): void
    {
        Http::fake();

        $this->assertSame(0.0, ClientDiscount::percent());
        $this->assertSame(0.0, ClientDiscount::percent(User::factory()->create(['crm_company_id' => null])));
        Http::assertNothingSent();
    }

    public function test_discount_comes_from_crm_and_is_cached(): void
    {
        Http::fake([
            'crm.example/api/bagheta/client-discount*' => Http::response(['discount_percent' => 16.1]),
        ]);

        $user = $this->client();

        $this->assertSame(16.1, ClientDiscount::percent($user));
        $this->assertSame(16.1, ClientDiscount::percent($user));

        Http::assertSentCount(1);
    }

    public function test_crm_error_means_no_discount(): void
    {
        Http::fake([
            'crm.example/*' => Http::response(['error' => 'boom'], 500),
        ]);

        $this->assertSame(0.0, ClientDiscount::percent($this->client()));
    }

    public function test_price_is_discounted_and_converted_to_a_stick(): void
    {
        Http::fake([
            'crm.example/*' => Http::response(['discount_percent' => 16.1]),
        ]);

        $user = $this->client();

        // База 8.017 $/м: 16.1 % → 6.7263 $/м, рейка 2.9 м → 19.51 $.
        $this->assertSame(6.7263, ClientDiscount::price(8.017, $user));
        $this->assertEqualsWithDelta(19.51, ClientDiscount::stickPrice(8.017, $user), 0.01);
    }

    public function test_catalog_page_shows_the_client_price(): void
    {
        Http::fake([
            'crm.example/*' => Http::response(['discount_percent' => 16.1]),
        ]);

        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);

        Product::query()->create([
            'category_id' => $category->id,
            'sku' => '100-01-01',
            'slug' => '100-01-01-client',
            'name_ru' => 'Пластиковый багет: 100-01-01',
            'price' => 8.017,
            'stock' => 10,
            'units_per_box' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->client())
            ->get(route('catalog.show', '100-01-01-client'))
            ->assertOk()
            ->assertSee('Ваша цена')
            ->assertSee('6.73')
            ->assertSee('скидка 16.1');
    }

    public function test_cart_uses_the_client_price(): void
    {
        Http::fake([
            'crm.example/*' => Http::response(['discount_percent' => 16.1]),
        ]);

        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => '200-02-02',
            'slug' => '200-02-02-client',
            'name_ru' => 'Пластиковый багет: 200-02-02',
            'price' => 10,
            'stock' => 10,
            'is_active' => true,
        ]);

        $user = $this->client();

        $this->actingAs($user)
            ->post(route('cart.store', $product), ['quantity' => 1])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('8.39');
    }
}
