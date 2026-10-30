<?php

namespace App\Services;

use App\Models\BatchFeedback;
use App\Models\ParticipantFeedback;
use App\Models\TrainingBatch;

class ParticipantFeedbackService
{
    /**
     * Recalculate and synchronize batch-level aggregate feedback from individual participant forms.
     */
    public function syncBatchAggregates(TrainingBatch $batch): BatchFeedback
    {
        $feedbacks = ParticipantFeedback::where('training_batch_id', $batch->id)->get();
        $count = $feedbacks->count();

        if ($count === 0) {
            return BatchFeedback::updateOrCreate(
                ['training_batch_id' => $batch->id],
                [
                    'q1_clarity_avg' => 0.00,
                    'q2_knowledge_avg' => 0.00,
                    'q3_relevance_avg' => 0.00,
                    'q4_handbook_avg' => 0.00,
                    'q5_overall_avg' => 0.00,
                    'total_forms_analyzed' => 0,
                ]
            );
        }

        $q1Avg = round((float) $feedbacks->avg('q1_clarity'), 2);
        $q2Avg = round((float) $feedbacks->avg('q2_knowledge'), 2);
        $q3Avg = round((float) $feedbacks->avg('q3_relevance'), 2);
        $q4Avg = round((float) $feedbacks->avg('q4_handbook'), 2);
        $q5Avg = round((float) $feedbacks->avg('q5_overall'), 2);

        // Collect unique qualitative highlights
        $highlights = $feedbacks->pluck('q6_most_useful')->filter(fn($v) => !empty(trim($v)))->unique()->take(8)->implode("\n• ");
        $improvements = $feedbacks->pluck('q7_improvements')->filter(fn($v) => !empty(trim($v)))->unique()->take(8)->implode("\n• ");
        $unanswered = $feedbacks->pluck('q8_unanswered')->filter(fn($v) => !empty(trim($v)))->unique()->take(8)->implode("\n• ");

        return BatchFeedback::updateOrCreate(
            ['training_batch_id' => $batch->id],
            [
                'q1_clarity_avg' => $q1Avg,
                'q2_knowledge_avg' => $q2Avg,
                'q3_relevance_avg' => $q3Avg,
                'q4_handbook_avg' => $q4Avg,
                'q5_overall_avg' => $q5Avg,
                'total_forms_analyzed' => $count,
                'qualitative_highlights' => $highlights ? '• ' . $highlights : null,
                'improvement_suggestions' => $improvements ? '• ' . $improvements : null,
                'unanswered_questions' => $unanswered ? '• ' . $unanswered : null,
            ]
        );
    }
}
