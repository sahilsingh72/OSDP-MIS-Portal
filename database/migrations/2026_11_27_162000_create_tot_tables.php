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
        Schema::create('tot_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 60)->unique();
            $table->string('title')->nullable();
            $table->date('training_date')->index();
            $table->string('venue_name');
            $table->string('venue_address')->nullable();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('status', 30)->default('scheduled')->index();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tot_batch_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tot_batch_id')->constrained('tot_batches')->onDelete('cascade');
            $table->foreignId('field_trainer_id')->constrained('field_trainers')->onDelete('cascade');
            $table->boolean('is_present')->default(false);
            $table->string('remarks', 500)->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();

            $table->unique(['tot_batch_id', 'field_trainer_id'], 'unique_tot_batch_trainer');
            $table->index('tot_batch_id');
            $table->index('field_trainer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tot_batch_trainers');
        Schema::dropIfExists('tot_batches');
    }
};
