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
        // 1. Master Participants
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('office_name')->nullable(); // Office / Gram Panchayat
            $table->string('phone_masked', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'designation']);
        });

        // 2. Batch Participants Enrollment
        Schema::create('batch_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');
            $table->unsignedSmallInteger('serial_number')->default(1);
            $table->timestamps();

            $table->unique(['training_batch_id', 'participant_id']);
            $table->index('training_batch_id');
        });

        // 3. Attendance Records (Form 2 Paper Transcription)
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_batch_id')->constrained('training_batches')->onDelete('cascade');
            $table->foreignId('batch_participant_id')->constrained('batch_participants')->onDelete('cascade');
            $table->boolean('morning_present')->default(false);
            $table->boolean('afternoon_present')->default(false);
            $table->string('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['training_batch_id', 'batch_participant_id']);
            $table->index('training_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('batch_participants');
        Schema::dropIfExists('participants');
    }
};
