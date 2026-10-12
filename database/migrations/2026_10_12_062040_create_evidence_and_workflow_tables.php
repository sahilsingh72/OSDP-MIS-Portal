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
        // 1. Evidence Uploads (6 mandatory evidence items stored privately)
        Schema::create('training_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->string('evidence_type', 40); // attendance_sheet, photo_1, photo_2, video, feedback_forms, batch_completion_report
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('file_hash', 64)->nullable(); // SHA-256
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('is_valid')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['training_batch_id', 'evidence_type']);
        });

        // 2. Batch Summaries (Form 3 Training Summary Part C)
        Schema::create('batch_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->unique()->constrained('training_batches')->onDelete('cascade');
            $table->unsignedInteger('registered_count')->default(0);
            $table->unsignedInteger('present_count')->default(0);
            $table->unsignedInteger('feedback_forms_received')->default(0);
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->boolean('pre_post_assessment_done')->default(false);
            $table->text('what_went_well')->nullable();
            $table->text('problems_faced')->nullable();
            $table->text('follow_up_actions')->nullable();
            $table->string('submitted_by_name')->nullable();
            $table->string('checked_by_name')->nullable();
            $table->date('checked_date')->nullable();
            $table->timestamps();
        });

        // 3. Batch Feedback Aggregates (Form 1 Ratings: 1 to 5)
        Schema::create('batch_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->unique()->constrained('training_batches')->onDelete('cascade');
            $table->decimal('q1_clarity_avg', 3, 2)->default(0.00);
            $table->decimal('q2_knowledge_avg', 3, 2)->default(0.00);
            $table->decimal('q3_relevance_avg', 3, 2)->default(0.00);
            $table->decimal('q4_handbook_avg', 3, 2)->default(0.00);
            $table->decimal('q5_overall_avg', 3, 2)->default(0.00);
            $table->unsignedInteger('total_forms_analyzed')->default(0);
            $table->text('qualitative_highlights')->nullable();
            $table->text('improvement_suggestions')->nullable();
            $table->text('unanswered_questions')->nullable();
            $table->timestamps();
        });

        // 4. Batch Reviews (DC and PMU review decisions & audit trail)
        Schema::create('batch_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('restrict');
            $table->string('role_at_review', 30); // district_coordinator, pmu, ocac
            $table->string('decision', 30); // verified, correction_requested, rejected, pack_checked
            $table->text('remarks')->nullable();
            $table->json('checklist_snapshot')->nullable();
            $table->timestamps();

            $table->index('training_batch_id');
        });

        // 5. Independent Field Verifications
        Schema::create('field_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('monitor_id')->constrained('users')->onDelete('restrict');
            $table->date('visit_date');
            $table->boolean('session_in_progress')->default(true);
            $table->unsignedInteger('verified_headcount')->default(0);
            $table->boolean('banner_displayed')->default(true);
            $table->boolean('kits_distributed')->default(true);
            $table->boolean('tea_lunch_provided')->default(true);
            $table->string('training_quality_rating', 20)->default('good'); // excellent, good, average, poor
            $table->text('observations')->nullable();
            $table->text('corrective_actions_needed')->nullable();
            $table->timestamps();

            $table->index('training_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_verifications');
        Schema::dropIfExists('batch_reviews');
        Schema::dropIfExists('batch_feedback');
        Schema::dropIfExists('batch_summaries');
        Schema::dropIfExists('training_uploads');
    }
};
