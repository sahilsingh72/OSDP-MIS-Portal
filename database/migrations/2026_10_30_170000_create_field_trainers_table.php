<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('field_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->unique();
            $table->string('designation');
            $table->string('department');
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('block_id')->nullable()->constrained('blocks')->nullOnDelete();
            $table->foreignId('gram_panchayat_id')->nullable()->constrained('gram_panchayats')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['district_id', 'block_id', 'gram_panchayat_id']);
        });

        // Backfill existing field-trainer users into field_trainers table
        $trainers = User::role('field-trainer')->get();
        foreach ($trainers as $trainerUser) {
            $phone = $trainerUser->phone;
            if (!$phone) {
                $phone = '98' . str_pad((string)$trainerUser->id, 8, '0', STR_PAD_LEFT);
            }

            // Ensure unique phone in case of conflict during backfill
            $counter = 1;
            while (DB::table('field_trainers')->where('phone', $phone)->exists()) {
                $phone = '97' . str_pad((string)($trainerUser->id * 10 + $counter), 8, '0', STR_PAD_LEFT);
                $counter++;
            }

            DB::table('field_trainers')->insert([
                'user_id' => $trainerUser->id,
                'name' => $trainerUser->name,
                'email' => $trainerUser->email,
                'phone' => $phone,
                'designation' => $trainerUser->designation ?? 'Field Master Trainer',
                'department' => $trainerUser->department ?? 'Training Division',
                'district_id' => $trainerUser->district_id ?? $trainerUser->assignedDistricts->first()?->id ?? 1101,
                'block_id' => $trainerUser->block_id,
                'gram_panchayat_id' => $trainerUser->gram_panchayat_id,
                'is_active' => $trainerUser->is_active ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_trainers');
    }
};
