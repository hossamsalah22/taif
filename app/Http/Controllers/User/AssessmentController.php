<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAssessmentRequest;
use App\Http\Resources\User\AssessmentResource;
use App\Models\Assessment;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Fetch the registration assessment test for a specific child.
     */
    public function registrationTest(Request $request, Child $child)
    {
        if ($child->parent_id !== auth('user')->id()) {
            return $this->failedResponse('Unauthorized access to this child profile.', 403);
        }

        $assessment = Assessment::with(['questions' => function ($query) {
            $query->orderBy('order');
        }])->where('autism_level', $child->autism_level->value)->where('status', 'active')->first();

        if (! $assessment) {
            return $this->failedResponse('No assessment found for this severity level.', 404);
        }

        $submissionsCount = $child->assessmentSubmissions()->where('assessment_id', $assessment->id)->count();

        if ($submissionsCount >= $assessment->max_attempts && ! $child->override_assessment_lock) {
            return $this->failedResponse('You have reached the maximum number of attempts allowed for this assessment. Please await specialist feedback.', 403);
        }

        return $this->successResponse(__('Retrieved Successfully'), new AssessmentResource($assessment));
    }

    /**
     * Submit answers for the registration assessment test.
     */
    public function submitTest(SubmitAssessmentRequest $request, Child $child)
    {
        $data = $request->validated();

        $assessment = Assessment::findOrFail($data['assessment_id']);

        if ($assessment->autism_level !== $child->autism_level) {
            return $this->failedResponse(__('Assessment version mismatch with child severity level.'), 400);
        }

        $submission = DB::transaction(function () use ($assessment, $child, $data) {
            // Create submission
            $submission = $assessment->submissions()->create([
                'child_id' => $child->id,
                'assessment_version' => $assessment->version,
                'status' => 'completed', // Or 'pending_review' depending on your frontend logic
            ]);

            // Create answers
            foreach ($data['answers'] as $answer) {
                $submission->answers()->create([
                    'question_id' => $answer['question_id'],
                    'answer_data' => $answer['answer_data'],
                ]);
            }

            if ($child->override_assessment_lock) {
                $child->update(['override_assessment_lock' => false]);
            }

            return $submission;
        });

        return $this->successResponse(__('Assessment submitted successfully.'));
    }
}
