<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Read-only view of etichete-crm client companies. The showcase user is linked
 * to a CRM company, so the admin picks a real company instead of retyping it.
 */
class CrmCompanyDirectory
{
    private const CACHE_KEY = 'crm-client-companies';

    /**
     * @return list<array{id: int, business: ?string, business_name: ?string, name: string, contact_person: ?string, email: ?string, phone: ?string, locale: ?string}>
     */
    public function all(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $url = (string) config('services.crm.companies_url');
        $token = (string) config('services.crm.catalog_token');

        if ($url === '') {
            return [];
        }

        try {
            $request = Http::timeout(15)->acceptJson();
            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request->get($url);
            if (! $response->successful()) {
                Log::warning('CRM companies request failed', ['status' => $response->status()]);

                return [];
            }

            $json = $response->json();
            $items = is_array($json) && isset($json['companies']) && is_array($json['companies'])
                ? $json['companies']
                : (is_array($json) ? $json : []);

            $companies = [];
            foreach ($items as $item) {
                if (! is_array($item) || ! isset($item['id'], $item['name'])) {
                    continue;
                }

                $companies[] = [
                    'id' => (int) $item['id'],
                    'business' => isset($item['business']) ? (string) $item['business'] : null,
                    'business_name' => isset($item['business_name']) ? (string) $item['business_name'] : null,
                    'name' => (string) $item['name'],
                    'contact_person' => isset($item['contact_person']) ? (string) $item['contact_person'] : null,
                    'email' => isset($item['email']) ? (string) $item['email'] : null,
                    'phone' => isset($item['phone']) ? (string) $item['phone'] : null,
                    'locale' => isset($item['locale']) ? (string) $item['locale'] : null,
                ];
            }

            if ($companies !== []) {
                Cache::put(self::CACHE_KEY, $companies, now()->addMinutes(5));
            }

            return $companies;
        } catch (Throwable $e) {
            Log::warning('CRM companies request exception: '.$e->getMessage());

            return [];
        }
    }

    /**
     * @return array{id: int, business: ?string, business_name: ?string, name: string, contact_person: ?string, email: ?string, phone: ?string, locale: ?string}|null
     */
    public function find(int $id): ?array
    {
        foreach ($this->all() as $company) {
            if ($company['id'] === $id) {
                return $company;
            }
        }

        return null;
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
