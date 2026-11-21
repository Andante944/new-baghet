<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Персональная скидка клиента Багета. Приходит из CRM по crm_company_id,
 * кэшируется на 10 минут: цены в CRM меняются редко, а страницы — часто.
 */
final class ClientDiscount
{
    private const TTL_MINUTES = 10;

    public static function percent(?User $user = null): float
    {
        $user ??= auth()->user();
        $companyId = (int) ($user?->crm_company_id ?? 0);

        if ($companyId < 1) {
            return 0.0;
        }

        $value = Cache::remember(
            self::cacheKey($companyId),
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($companyId): float {
                $url = self::url();
                if ($url === '') {
                    return 0.0;
                }

                try {
                    $response = Http::withToken((string) config('services.crm.catalog_token'))
                        ->acceptJson()
                        ->timeout(5)
                        ->get($url, ['company_id' => $companyId]);

                    if (! $response->ok()) {
                        return 0.0;
                    }

                    return round((float) $response->json('discount_percent', 0), 2);
                } catch (\Throwable) {
                    return 0.0;
                }
            },
        );

        return (float) $value;
    }

    /** Цена за метр/упаковку со скидкой клиента. */
    public static function price(float $base, ?User $user = null): float
    {
        $percent = self::percent($user);

        return $percent > 0
            ? round($base * (1 - $percent / 100), 4)
            : $base;
    }

    /** Та же цена, пересчитанная на рейку (2.9 м). */
    public static function stickPrice(float $basePerMeter, ?User $user = null): float
    {
        $length = (float) config('catalog.stick_length_m', 2.9);

        return round(self::price($basePerMeter, $user) * ($length > 0 ? $length : 2.9), 4);
    }

    public static function forget(?int $companyId): void
    {
        if ($companyId !== null && $companyId > 0) {
            Cache::forget(self::cacheKey($companyId));
        }
    }

    private static function cacheKey(int $companyId): string
    {
        return 'baghet:client-discount:'.$companyId;
    }

    private static function url(): string
    {
        $catalog = trim((string) config('services.crm.catalog_url'));

        if ($catalog === '') {
            return '';
        }

        return (string) preg_replace('#/catalog/?$#', '', $catalog).'/client-discount';
    }
}
