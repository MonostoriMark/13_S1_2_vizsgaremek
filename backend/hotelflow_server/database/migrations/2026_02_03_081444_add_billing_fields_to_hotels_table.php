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
        if (Schema::hasTable('hotels')) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->string('tax_number')->nullable()->after('starRating');
                $table->string('bank_account')->nullable()->after('tax_number');
                $table->string('eu_tax_number')->nullable()->after('bank_account');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hotels')) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->dropColumn(['tax_number', 'bank_account', 'eu_tax_number']);
            });
        }
    }
};
