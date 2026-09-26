<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('prestashop_id')->nullable()->unique();
            $table->string('sku')->index();
            $table->string('slug')->unique();
            $table->string('name_ru');
            $table->string('name_ro')->nullable();
            $table->text('description_ru')->nullable();
            $table->text('description_ro')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('width')->nullable()->index();
            $table->string('color')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->string('image_path')->nullable();
            $table->string('source_url')->nullable();
            $table->unsignedBigInteger('bitrix_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
