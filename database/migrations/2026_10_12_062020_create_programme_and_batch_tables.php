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
        // 1. Master Training Plans
        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('financial_year', 15)->default('2026-2027');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('target_batches')->default(2149);
            $table->unsignedInteger('target_participants')->default(41620);
            $table->string('status', 20)->default('active'); // draft, active, completed, archived
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Training Batches
        Schema::create('training_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 40)->unique();
            $table->foreignId('training_plan_id')->constrained('training_plans')->onDelete('cascade');
            $table->foreignId('training_tier_id')->constrained('training_tiers')->onDelete('restrict');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('restrict');
            $table->foreignId('block_id')->nullable()->constrained('blocks')->onDelete('restrict');
            $table->foreignId('venue_id')->nullable()->constrained('venues')->onDelete('set null');
            
            $table->date('scheduled_date');
            $table->time('start_time')->default('10:00:00');
            $table->time('end_time')->default('17:30:00');
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();

            // Status state machine: planned, scheduled, in_progress, awaiting_evidence, submitted, under_review, correction_required, verified, rejected, closed
            $table->string('status', 30)->default('planned');
            
            $table->unsignedInteger('planned_participants')->default(30);
            $table->unsignedInteger('registered_participants')->default(0);
            $table->unsignedInteger('present_participants')->default(0);

            $table->timestamp('submission_deadline')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('scheduled_date');
            $table->index('status');
            $table->index(['district_id', 'status']);
            $table->index(['training_tier_id', 'status']);
        });

        // 3. Batch Trainers Pivot
        Schema::create('batch_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role', 20)->default('primary'); // primary, co-trainer
            $table->timestamps();

            $table->unique(['training_batch_id', 'user_id']);
        });

        // 4. Batch Gram Panchayats (Supports cluster batches with multiple GPs)
        Schema::create('batch_gram_panchayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('gram_panchayat_id')->constrained('gram_panchayats')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['training_batch_id', 'gram_panchayat_id']);
        });

        // 5. Batch Modules Delivered
        Schema::create('batch_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('training_module_id')->constrained('training_modules')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['training_batch_id', 'training_module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_modules');
        Schema::dropIfExists('batch_gram_panchayats');
        Schema::dropIfExists('batch_trainers');
        Schema::dropIfExists('training_batches');
        Schema::dropIfExists('training_plans');
    }
};
