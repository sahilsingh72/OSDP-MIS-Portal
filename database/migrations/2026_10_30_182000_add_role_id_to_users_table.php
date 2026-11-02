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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id')
                ->constrained('roles')
                ->nullOnDelete();
        });

        // Backfill role_id from model_has_roles for existing users
        $userRoles = DB::table('model_has_roles')
            ->where('model_type', 'App\Models\User')
            ->get();

        foreach ($userRoles as $ur) {
            DB::table('users')
                ->where('id', $ur->model_id)
                ->whereNull('role_id')
                ->update(['role_id' => $ur->role_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
