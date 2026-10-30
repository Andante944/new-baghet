<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmCatalogSync
{
    /** Длина палки багета: в CRM цена за палку, на витрине — за метр. */
    private const STICK_LENGTH_M = 2.9;

    /**
     * Pull bagheta name/sale_price from etichete-crm and bind by sku.
     *
     * @return array{matched: int, updated: int, unbound: int, missing_local: int}
     */
    public function sync(): array
    {
        $stats = [
            'matched' => 0,
            'updated' => 0,
            'unbound' => 0,
            'missing_local' => 0,
        ];

        $items = $this->fetchCatalog();
        $bySku = [];

        foreach ($items as $item) {
            $sku = trim((string) ($item['sku'] ?? ''));
            if ($sku === '') {
                continue;
            }
            $bySku[$sku] = $item;
        }

        $products = Product::query()->get();

        foreach ($products as $product) {
            $item = $bySku[$product->sku] ?? null;

            if ($item === null) {
                $stats['unbound']++;

                continue;
            }

            $stats['matched']++;

            $crmId = (int) ($item['id'] ?? 0);
            $name = trim((string) ($item['name'] ?? ''));
            // Витрина показывает цену за метр: берём sale_price_per_meter,
            // а если её нет — делим цену палки на её длину.
            $price = null;
            if (isset($item['sale_price_per_meter']) && (float) $item['sale_price_per_meter'] > 0) {
                $price = round((float) $item['sale_price_per_meter'], 4);
            } elseif (isset($item['sale_price']) && (float) $item['sale_price'] > 0) {
                $price = round((float) $item['sale_price'] / self::STICK_LENGTH_M, 4);
            }

            $payload = [];
            if ($crmId > 0 && (int) $product->crm_product_id !== $crmId) {
                // Один sku может встретиться дважды на сайте — привязку
                // (уникальную) получает только первая карточка.
                $takenByOther = Product::query()
                    ->where('crm_product_id', $crmId)
                    ->whereKeyNot($product->id)
                    ->exists();

                if (! $takenByOther) {
                    $payload['crm_product_id'] = $crmId;
                }
            }
            if ($name !== '' && $product->name_ru !== $name) {
                $payload['name_ru'] = $name;
            }
            if ($price !== null && (float) $product->price !== $price) {
                $payload['price'] = $price;
            }

            if ($payload === []) {
                continue;
            }

            $product->update($payload);
            $stats['updated']++;
        }

        $localSkus = $products->pluck('sku')->all();
        foreach (array_keys($bySku) as $sku) {
            if (! in_array($sku, $localSkus, true)) {
                $stats['missing_local']++;
            }
        }

        return $stats;
    }

    /**
     * @return list<array{id?: int|string, sku?: string, name?: string, sale_price?: float|string|null}>
     */
    public function fetchCatalog(): array
    {
        $url = (string) config('services.crm.catalog_url');
        $token = (string) config('services.crm.catalog_token');

        if ($url === '') {
            throw new \RuntimeException('CRM_CATALOG_URL is not configured.');
        }

        $request = Http::timeout(60)->acceptJson();
        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->get($url)->throw();
        $json = $response->json();

        if (! is_array($json)) {
            throw new \RuntimeException('CRM catalog response is not JSON.');
        }

        if (array_is_list($json)) {
            return $json;
        }

        foreach (['data', 'products', 'items'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                return array_values($json[$key]);
            }
        }

        Log::warning('CRM catalog: unexpected payload shape', ['keys' => array_keys($json)]);

        return [];
    }
}
