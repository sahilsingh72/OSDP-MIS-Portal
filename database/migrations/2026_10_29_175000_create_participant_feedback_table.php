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
        Schema::create('participant_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('batch_participant_id')->constrained('batch_participants')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');

            // Form 1 Header snapshots
            $table->string('training_tier_snapshot', 50)->nullable();
            $table->string('venue_snapshot')->nullable();
            $table->date('session_date')->nullable();
            $table->string('designation_office_snapshot')->nullable();
            $table->string('participant_name_snapshot')->nullable();

            // RATE THE SESSION (1 = Poor, 5 = Excellent)
            $table->unsignedTinyInteger('q1_clarity'); // 1. Clarity of the training content
            $table->unsignedTinyInteger('q2_knowledge'); // 2. Trainer's knowledge of the subject
            $table->unsignedTinyInteger('q3_relevance'); // 3. Relevance of examples to your daily work
            $table->unsignedTinyInteger('q4_handbook'); // 4. Usefulness of the Participant Handbook
            $table->unsignedTinyInteger('q5_overall'); // 5. Overall satisfaction with the session

            // Calculated average rating for this specific participant
            $table->decimal('average_rating', 3, 2)->default(0.00);

            // IN YOUR OWN WORDS
            $table->text('q6_most_useful')->nullable(); // What was the most useful part of today's session?
            $table->text('q7_improvements')->nullable(); // What could be improved?
            $table->text('q8_unanswered')->nullable(); // Any question that was not answered during the session?

            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['training_batch_id', 'batch_participant_id'], 'unique_batch_participant_feedback');
            $table->index('training_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_feedback');
    }
};
