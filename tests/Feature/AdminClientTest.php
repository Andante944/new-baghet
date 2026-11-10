<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminClientTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_client_without_bitrix_ids_and_password(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->post(route('admin.clients.store'), [
                'name' => 'Ion Popescu',
                'company_name' => 'SRL Exemplu',
                'email' => 'ion@example.com',
                'phone' => '+37360000000',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.clients.index'))
            ->assertSessionHas('generated_password');

        $client = User::query()->where('email', 'ion@example.com')->firstOrFail();
        $this->assertSame('client', $client->role);
        $this->assertSame('SRL Exemplu', $client->company_name);
        $this->assertNull($client->bitrix_contact_id);
        $this->assertNull($client->bitrix_company_id);
        $this->assertTrue($client->is_active);

        $generated = $response->getSession()->get('generated_password');
        $this->assertSame('ion@example.com', $generated['email']);
        $this->assertTrue(Hash::check($generated['password'], $client->password));
    }

    public function test_admin_can_import_clients_from_csv(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        User::query()->create([
            'name' => 'Existing',
            'email' => 'exists@example.com',
            'password' => 'Password123',
            'role' => 'client',
            'is_active' => true,
        ]);

        $csv = implode("\n", [
            'company_name;name;email;phone;password',
            'SRL Test;Ion Popescu;ion@example.com;+37360000000;',
            'SRL Test 2;Maria Ionescu;maria@example.com;+37360000001;Password123',
            'SRL Existing;Existing;exists@example.com;;',
            'SRL Bad;Bad Email;not-an-email;;',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.clients.import.store'), ['rows' => $csv])
            ->assertOk();

        $this->assertSame(2, count($response->viewData('created')));
        $this->assertSame(1, count($response->viewData('skipped')));
        $this->assertSame(1, count($response->viewData('importErrors')));

        $ion = User::query()->where('email', 'ion@example.com')->firstOrFail();
        $this->assertSame('client', $ion->role);
        $this->assertTrue($ion->is_active);
        $this->assertTrue(Hash::check('Password123', User::query()->where('email', 'maria@example.com')->firstOrFail()->password));
        $this->assertNotSame('', (string) $ion->password);

        $token = $response->viewData('token');
        $this->assertNotNull($token);

        $download = $this->actingAs($admin)
            ->get(route('admin.clients.import.download', $token))
            ->assertOk();

        $content = $download->streamedContent();
        $this->assertStringContainsString('ion@example.com', $content);
        $this->assertStringContainsString('company_name;name;email;phone;crm_company_id;password', $content);
    }

    public function test_import_template_is_downloadable(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.clients.import.template'))
            ->assertOk()
            ->assertDownload('client-import-template.csv');
    }

    public function test_crm_template_lists_unlinked_companies(): void
    {
        Cache::flush();
        Http::fake([
            'https://crm.test/api/bagheta/companies*' => Http::response([
                'companies' => [
                    ['id' => 42, 'name' => 'SRL Linked', 'contact_person' => 'Linked', 'email' => null, 'phone' => null],
                    ['id' => 43, 'name' => 'SRL Free', 'contact_person' => 'Free', 'email' => 'free@example.com', 'phone' => '+37360000001'],
                ],
            ]),
        ]);
        config([
            'services.crm.companies_url' => 'https://crm.test/api/bagheta/companies',
            'services.crm.catalog_token' => 'secret-token',
        ]);

        User::query()->create([
            'name' => 'Linked',
            'email' => 'linked@example.com',
            'password' => 'Password123',
            'role' => 'client',
            'crm_company_id' => 42,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.clients.import.crm-template'))
            ->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('SRL Free', $content);
        $this->assertStringContainsString('"43"', $content);
        $this->assertStringNotContainsString('SRL Linked', $content);
    }

    public function test_admin_can_link_client_to_crm_company(): void
    {
        Cache::flush();
        Http::fake([
            'https://crm.test/api/bagheta/companies*' => Http::response([
                'companies' => [
                    [
                        'id' => 42,
                        'business' => 'bagheta',
                        'business_name' => 'Bagheta',
                        'name' => 'SRL CRM',
                        'contact_person' => 'Ion Popescu',
                        'email' => 'ion@example.com',
                        'phone' => '+37360000000',
                        'locale' => 'RO',
                    ],
                ],
            ]),
        ]);
        config([
            'services.crm.companies_url' => 'https://crm.test/api/bagheta/companies',
            'services.crm.catalog_token' => 'secret-token',
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.clients.create'))
            ->assertOk()
            ->assertSee('SRL CRM');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'business=etichete'));

        $this->actingAs($admin)
            ->post(route('admin.clients.store'), [
                'name' => 'Ion Popescu',
                'company_name' => 'SRL CRM',
                'email' => 'ion@example.com',
                'crm_company_id' => 42,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.clients.index'));

        $this->assertSame(42, User::query()->where('email', 'ion@example.com')->firstOrFail()->crm_company_id);
    }

    public function test_crm_company_can_be_linked_only_once(): void
    {
        Cache::flush();
        Http::fake([
            'https://crm.test/api/bagheta/companies*' => Http::response([
                'companies' => [
                    ['id' => 42, 'name' => 'SRL CRM', 'business_name' => 'Bagheta'],
                ],
            ]),
        ]);
        config([
            'services.crm.companies_url' => 'https://crm.test/api/bagheta/companies',
            'services.crm.catalog_token' => 'secret-token',
        ]);

        User::query()->create([
            'name' => 'First',
            'email' => 'first@example.com',
            'password' => 'Password123',
            'role' => 'client',
            'crm_company_id' => 42,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.clients.store'), [
                'name' => 'Second',
                'email' => 'second@example.com',
                'crm_company_id' => 42,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('crm_company_id');
    }

    public function test_import_can_link_clients_to_crm_companies(): void
    {
        Cache::flush();
        Storage::fake('local');
        Http::fake([
            'https://crm.test/api/bagheta/companies*' => Http::response([
                'companies' => [
                    ['id' => 42, 'name' => 'SRL CRM', 'business_name' => 'Bagheta'],
                    ['id' => 43, 'name' => 'SRL CRM 2', 'business_name' => 'Bagheta'],
                ],
            ]),
        ]);
        config([
            'services.crm.companies_url' => 'https://crm.test/api/bagheta/companies',
            'services.crm.catalog_token' => 'secret-token',
        ]);

        $csv = implode("\n", [
            'company_name;name;email;phone;crm_company_id',
            'SRL CRM;Ion Popescu;ion@example.com;+37360000000;42',
            'SRL CRM;Second Contact;second@example.com;;42',
        ]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.clients.import.store'), ['rows' => $csv])
            ->assertOk();

        $this->assertSame(1, count($response->viewData('created')));
        $this->assertSame(1, count($response->viewData('skipped')));
        $this->assertSame(42, User::query()->where('email', 'ion@example.com')->firstOrFail()->crm_company_id);
    }

    public function test_guest_and_client_cannot_import_clients(): void
    {
        $this->get(route('admin.clients.import'))->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $this->actingAs($client)
            ->get(route('admin.clients.import'))
            ->assertForbidden();
    }
}
