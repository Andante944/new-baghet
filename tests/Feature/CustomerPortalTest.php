<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_cabinet(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_live_search_returns_matching_products_without_authentication(): void
    {
        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);
        Product::query()->create([
            'category_id' => $category->id,
            'sku' => '100-01-01',
            'slug' => '100-01-01-search',
            'name_ru' => 'Пластиковый багет: 100-01-01',
            'price' => 10,
            'stock' => 20,
            'is_active' => true,
        ]);

        $this->getJson(route('catalog.search', ['q' => '100-01']))
            ->assertOk()
            ->assertJsonPath('products.0.sku', '100-01-01')
            ->assertJsonPath('products.0.price', '13.00');
    }

    public function test_admin_can_log_in_and_create_a_client(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => 'secret-password',
        ]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.clients.create'))->assertOk();

        $this->post(route('admin.clients.store'), [
            'name' => 'Test Client',
            'company_name' => 'Client SRL',
            'email' => 'client@example.com',
            'password' => 'client-password',
            'phone' => '+37360000000',
            'is_active' => '1',
        ])->assertRedirect(route('admin.clients.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'client@example.com',
            'role' => 'client',
            'company_name' => 'Client SRL',
            'is_active' => true,
        ]);
    }

    public function test_client_can_add_product_and_submit_order_at_wholesale_price(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
        ]);
        $category = Category::query()->create([
            'slug' => 'plastic',
            'name_ru' => 'Пластиковый багет',
            'name_ro' => 'Baghetă plastic',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => '100-01-01',
            'slug' => '100-01-01-test',
            'name_ru' => 'Пластиковый багет: 100-01-01',
            'price' => 10,
            'stock' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($client)
            ->post(route('cart.store', $product), ['quantity' => 3])
            ->assertRedirect(route('cart.index'));

        $this->actingAs($client)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('100-01-01');

        $this->actingAs($client)
            ->post(route('orders.store'), ['notes' => 'Test order'])
            ->assertRedirect();

        $order = Order::query()->with('items')->firstOrFail();

        $this->assertSame($client->id, $order->user_id);
        $this->assertSame('30.00', $order->subtotal);
        $this->assertCount(1, $order->items);
        $this->assertSame('10.00', $order->items->first()->unit_price);
        $this->assertSame(3, $order->items->first()->quantity);

        $this->actingAs($client)->get(route('dashboard'))->assertOk();
        $this->actingAs($client)->get(route('orders.show', $order))->assertOk();
    }
}
