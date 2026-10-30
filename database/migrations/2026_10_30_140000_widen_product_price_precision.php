<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Цена за метр у багета трёхзначная (0.597) — двух знаков не хватает.
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 12, 4)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->change();
        });
    }
};
