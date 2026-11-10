<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['bitrix_contact_id']);
            $table->dropIndex(['bitrix_company_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bitrix_contact_id', 'bitrix_company_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('bitrix_contact_id')->nullable()->index();
            $table->unsignedBigInteger('bitrix_company_id')->nullable()->index();
        });
    }
};
