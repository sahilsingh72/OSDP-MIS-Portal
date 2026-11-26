<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participant_feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('participant_feedback', 'comments_suggestions')) {
                $table->text('comments_suggestions')->nullable()->after('average_rating');
            }
        });

        Schema::table('batch_feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('batch_feedback', 'comments_suggestions')) {
                $table->text('comments_suggestions')->nullable()->after('total_forms_analyzed');
            }
        });

        // Migrate existing comments data if available
        try {
            DB::statement("
                UPDATE participant_feedback 
                SET comments_suggestions = TRIM(CONCAT_WS(' ', 
                    NULLIF(COALESCE(q6_most_useful, ''), ''), 
                    NULLIF(COALESCE(q7_improvements, ''), ''), 
                    NULLIF(COALESCE(q8_unanswered, ''), '')
                ))
                WHERE (comments_suggestions IS NULL OR comments_suggestions = '')
                  AND (q6_most_useful IS NOT NULL OR q7_improvements IS NOT NULL OR q8_unanswered IS NOT NULL)
            ");

            DB::statement("
                UPDATE batch_feedback 
                SET comments_suggestions = TRIM(CONCAT_WS(' ', 
                    NULLIF(COALESCE(qualitative_highlights, ''), ''), 
                    NULLIF(COALESCE(improvement_suggestions, ''), ''), 
                    NULLIF(COALESCE(unanswered_questions, ''), '')
                ))
                WHERE (comments_suggestions IS NULL OR comments_suggestions = '')
                  AND (qualitative_highlights IS NOT NULL OR improvement_suggestions IS NOT NULL OR unanswered_questions IS NOT NULL)
            ");
        } catch (\Throwable $e) {
            // Non-critical backfill
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_feedback', function (Blueprint $table) {
            if (Schema::hasColumn('participant_feedback', 'comments_suggestions')) {
                $table->dropColumn('comments_suggestions');
            }
        });

        Schema::table('batch_feedback', function (Blueprint $table) {
            if (Schema::hasColumn('batch_feedback', 'comments_suggestions')) {
                $table->dropColumn('comments_suggestions');
            }
        });
    }
};
