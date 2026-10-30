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
            $table->foreignId('district_id')->nullable()->after('department')->constrained('districts')->nullOnDelete();
            $table->foreignId('block_id')->nullable()->after('district_id')->constrained('blocks')->nullOnDelete();
            $table->foreignId('gram_panchayat_id')->nullable()->after('block_id')->constrained('gram_panchayats')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropForeign(['block_id']);
            $table->dropForeign(['gram_panchayat_id']);
            $table->dropColumn(['district_id', 'block_id', 'gram_panchayat_id']);
        });
    }
};
