<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL/MariaDB doesn't support direct enum modification, so we use raw SQL
        if (Schema::hasTable('booking_payments')) {
            DB::statement("ALTER TABLE booking_payments MODIFY COLUMN method ENUM('bank_transfer', 'card') DEFAULT 'bank_transfer'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_payments')) {
            // Before reverting, update any 'card' values to 'bank_transfer'
            DB::table('booking_payments')
                ->where('method', 'card')
                ->update(['method' => 'bank_transfer']);
            
            // Then revert the enum
            DB::statement("ALTER TABLE booking_payments MODIFY COLUMN method ENUM('bank_transfer') DEFAULT 'bank_transfer'");
        }
    }
};
