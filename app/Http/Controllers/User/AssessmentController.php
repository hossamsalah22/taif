<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAssessmentRequest;
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
        if ($child->parent_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this child profile.');
        }

        $severityLevel = $child->autism_spectrum_level ?? 'low'; // Default to low if not specified

        $assessment = Assessment::with(['questions' => function ($query) {
            $query->orderBy('order');
        }])->where('severity_level', strtolower($severityLevel))->first();

        if (! $assessment) {
            return response()->json(['message' => 'No assessment found for this severity level.'], 404);
        }

        return response()->json([
            'assessment' => $assessment,
        ]);
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
