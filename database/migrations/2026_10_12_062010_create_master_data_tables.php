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
        // 1. Districts (30 districts of Odisha)
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 10)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Blocks (314 blocks of Odisha)
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('name');
            $table->string('code', 15)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['district_id', 'name']);
            $table->index('district_id');
        });

        // 3. Gram Panchayats
        Schema::create('gram_panchayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('blocks')->onDelete('cascade');
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['block_id', 'name']);
            $table->index('block_id');
        });

        // 4. User Districts Assignment (for District Coordinators & Field Monitors)
        Schema::create('user_districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'district_id']);
        });

        // 5. Training Tiers (Directorate, District, Block, Gram Panchayat, Citizen Awareness)
        Schema::create('training_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // directorate, district, block, gram_panchayat, citizen_awareness
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_duration_hours', 4, 1)->default(7.5);
            $table->unsignedInteger('target_batch_size')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Training Modules (Modules 1 through 8)
        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('module_number')->unique();
            $table->string('code', 20)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('topics')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Training Venues
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignId('block_id')->nullable()->constrained('blocks')->onDelete('set null');
            $table->string('name');
            $table->string('address')->nullable();
            $table->unsignedInteger('capacity')->default(50);
            $table->json('facilities')->nullable(); // projector, ac, internet, sound_system
            $table->string('contact_person')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('district_id');
            $table->index('block_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
        Schema::dropIfExists('training_modules');
        Schema::dropIfExists('training_tiers');
        Schema::dropIfExists('user_districts');
        Schema::dropIfExists('gram_panchayats');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('districts');
    }
};
