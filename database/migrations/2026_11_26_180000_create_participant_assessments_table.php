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
        Schema::create('participant_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('batch_participant_id')->constrained('batch_participants')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');

            // Snapshot fields matching the physical form header
            $table->string('training_tier_snapshot', 50)->nullable();
            $table->string('venue_snapshot')->nullable();
            $table->date('session_date')->nullable();
            $table->string('designation_office_snapshot')->nullable();
            $table->string('participant_name_snapshot')->nullable();

            // PART A: PRE-ASSESSMENT (6 Questions, Yes = 1, No = 0)
            $table->boolean('pre_q1')->nullable();
            $table->boolean('pre_q2')->nullable();
            $table->boolean('pre_q3')->nullable();
            $table->boolean('pre_q4')->nullable();
            $table->boolean('pre_q5')->nullable();
            $table->boolean('pre_q6')->nullable();
            $table->unsignedTinyInteger('pre_score')->nullable(); // count of 'Yes' responses (0 to 6)
            $table->boolean('pre_completed')->default(false);

            // PART B: POST-ASSESSMENT (6 Multiple Choice Questions, Choices A, B, C, D)
            $table->string('post_q1', 2)->nullable();
            $table->string('post_q2', 2)->nullable();
            $table->string('post_q3', 2)->nullable();
            $table->string('post_q4', 2)->nullable();
            $table->string('post_q5', 2)->nullable();
            $table->string('post_q6', 2)->nullable();
            $table->unsignedTinyInteger('post_score')->nullable(); // count of correct answers (0 to 6)
            $table->boolean('post_completed')->default(false);

            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['training_batch_id', 'batch_participant_id'], 'unique_batch_participant_assessment');
            $table->index('training_batch_id');
            $table->index('batch_participant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_assessments');
    }
};
