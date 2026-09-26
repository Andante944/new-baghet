<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::query()->updateOrCreate(
            ['slug' => 'plastic'],
            [
                'name_ru' => 'Пластиковый багет',
                'name_ro' => 'Baghetă plastic',
                'prestashop_id' => 11,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'hardware'],
            [
                'name_ru' => 'Фурнитура',
                'name_ro' => 'Feronerie',
                'prestashop_id' => 16,
            ]
        );
    }
}
