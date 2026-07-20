<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAssessmentRequest;
use App\Http\Resources\User\AssessmentResource;
use App\Models\Assessment;
use App\Models\Child;
use Illuminate\Http\Request;

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

        $severityLevel = $child->autism_level;

        $assessment = Assessment::with(['questions' => function ($query) {
            $query->orderBy('order');
        }])->where('autism_level', $severityLevel->value)->first();

        if (! $assessment) {
            return $this->failedResponse('No assessment found for this severity level.', 404);
        }

        return $this->successResponse(__('Retrieved Successfully'), new AssessmentResource($assessment));
    }

    /**
     * Submit answers for the registration assessment test.
     */
    public function submitTest(SubmitAssessmentRequest $request, Child $child)
    {
        $data = $request->validated();

        // Find the assessment based on child's severity level (assuming they submit the one they fetched)
        $severityLevel = $child->autism_spectrum_level ?? 'low';
        $assessment = Assessment::where('severity_level', strtolower($severityLevel))->firstOrFail();

        // Create submission
        $submission = $assessment->submissions()->create([
            'child_id' => $child->id,
            'status' => 'completed',
        ]);

        // Create answers
        foreach ($data['answers'] as $answer) {
            $submission->answers()->create([
                'question_id' => $answer['question_id'],
                'answer_data' => $answer['answer_data'],
            ]);
        }

        return response()->json([
            'message' => 'Assessment submitted successfully.',
            'submission_id' => $submission->id,
        ]);
    }
}
