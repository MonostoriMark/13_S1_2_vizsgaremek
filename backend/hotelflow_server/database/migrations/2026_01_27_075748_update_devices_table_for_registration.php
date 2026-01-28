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
        // First, remove any duplicate hotels_id entries (keep only the first one)
        if (Schema::hasTable('devices')) {
            \DB::statement('DELETE d1 FROM devices d1
                INNER JOIN devices d2 
                WHERE d1.id > d2.id AND d1.hotels_id = d2.hotels_id AND d1.hotels_id IS NOT NULL');
        }
        
        Schema::table('devices', function (Blueprint $table) {
            // Add new columns
            if (!Schema::hasColumn('devices', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('devices', 'token')) {
                $table->string('token', 64)->unique()->nullable()->after('hotels_id');
            }
            if (!Schema::hasColumn('devices', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('token');
            }
            if (!Schema::hasColumn('devices', 'created_at')) {
                $table->timestamps();
            }
            
            // Make hotels_id unique (one device per hotel) - only if not already unique
            if (Schema::hasColumn('devices', 'hotels_id')) {
                $indexes = \DB::select("SHOW INDEXES FROM devices WHERE Column_name = 'hotels_id' AND Key_name != 'PRIMARY'");
                if (empty($indexes)) {
                    $table->unique('hotels_id');
                }
            }
            
            // Drop old deviceApiValidation column if it exists
            if (Schema::hasColumn('devices', 'deviceApiValidation')) {
                $table->dropColumn('deviceApiValidation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropUnique(['hotels_id']);
            $table->dropColumn(['name', 'token', 'is_active', 'created_at', 'updated_at']);
            $table->integer('deviceApiValidation')->nullable();
        });
    }
};
