<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('client')->index();
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('bitrix_contact_id')->nullable()->index();
            $table->unsignedBigInteger('bitrix_company_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'company_name',
                'phone',
                'bitrix_contact_id',
                'bitrix_company_id',
                'is_active',
            ]);
        });
    }
};
