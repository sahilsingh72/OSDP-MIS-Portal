<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_records', 'is_present')) {
                $table->boolean('is_present')->default(false)->after('batch_participant_id');
            }
        });

        // Backfill is_present from existing morning_present or afternoon_present
        try {
            DB::statement("
                UPDATE attendance_records 
                SET is_present = (morning_present = 1 OR afternoon_present = 1)
            ");
        } catch (\Throwable $e) {
            // Ignore if statement fails in driver
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_records', 'is_present')) {
                $table->dropColumn('is_present');
            }
        });
    }
};
