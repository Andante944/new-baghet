<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'slug',
        'name_ru',
        'name_ro',
        'prestashop_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $locale === 'ro' ? ($this->name_ro ?: $this->name_ru) : $this->name_ru;
    }
}
