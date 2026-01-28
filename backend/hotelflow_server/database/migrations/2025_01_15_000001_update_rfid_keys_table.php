<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfidKeys', function (Blueprint $table) {
            // Add new columns
            $table->string('label')->nullable()->after('rfidKey');
            $table->enum('status', ['available', 'assigned', 'lost', 'disabled'])->default('available')->after('label');
            $table->timestamps();
            
            // Rename isUsed column (we'll keep it for backward compatibility but use status instead)
            // Actually, let's keep isUsed for now and sync it with status
        });

        // Update existing records
        DB::table('rfidKeys')->where('isUsed', 0)->update(['status' => 'available']);
        DB::table('rfidKeys')->where('isUsed', 1)->update(['status' => 'assigned']);
    }

    public function down(): void
    {
        Schema::table('rfidKeys', function (Blueprint $table) {
            $table->dropColumn(['label', 'status', 'created_at', 'updated_at']);
        });
    }
};
