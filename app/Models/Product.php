<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'prestashop_id',
        'sku',
        'slug',
        'name_ru',
        'name_ro',
        'description_ru',
        'description_ro',
        'price',
        'stock',
        'width',
        'color',
        'type',
        'image_path',
        'source_url',
        'bitrix_id',
        'crm_product_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'stock' => 'integer',
            'crm_product_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function isBoundToCrm(): bool
    {
        return $this->crm_product_id !== null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $locale === 'ro' ? ($this->name_ro ?: $this->name_ru) : $this->name_ru;
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return $locale === 'ro' ? ($this->description_ro ?: $this->description_ru) : $this->description_ru;
    }

    /**
     * Описание из PrestaShop приходит с inline-стилями (шрифт, цвет, размер) —
     * они ломают вёрстку витрины. Оставляем только базовую разметку.
     */
    public function descriptionHtml(?string $locale = null): ?string
    {
        $html = $this->localizedDescription($locale);
        if ($html === null || trim($html) === '') {
            return null;
        }

        $html = preg_replace('/\s(?:style|class|align|width|height|color|face|size|valign|border)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = strip_tags((string) $html, '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4>');

        return trim((string) $html) === '' ? null : $html;
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }

    /** Wholesale / opt price from catalog */
    public function wholesalePrice(): float
    {
        return (float) $this->price;
    }

    /** Guest retail = wholesale + 30% */
    public function retailPrice(): float
    {
        return round($this->wholesalePrice() * 1.3, 2);
    }

    /** Price shown depending on auth */
    public function displayPrice(): float
    {
        return auth()->check() ? $this->wholesalePrice() : $this->retailPrice();
    }
}

