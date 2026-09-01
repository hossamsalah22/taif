<?php

namespace App\Http\Controllers\User;

use App\Enums\ExerciseTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAssessmentRequest;
use App\Http\Resources\User\AssessmentResource;
use App\Models\Assessment;
use App\Models\Child;
use App\Models\Question;
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
            $attemptNumber = $child->assessmentSubmissions()->where('assessment_id', $assessment->id)->count() + 1;

            $correctAnswersCount = 0;
            $totalGradableQuestions = 0;

            foreach ($data['answers'] as $answerData) {
                $question = Question::with('options')->find($answerData['question_id']);
                if (! $question) {
                    continue;
                }

                $type = $question->exercise_type;
                $isCorrect = false;

                if (in_array($type, [ExerciseTypeEnum::IMAGE_SELECTION, ExerciseTypeEnum::DISTINGUISHING, ExerciseTypeEnum::AUDIO_FLASHCARDS])) {
                    $totalGradableQuestions++;
                    $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
                    $submittedOptionIds = (array) $answerData['answer_data'];

                    if (count($correctOptionIds) === count($submittedOptionIds) && empty(array_diff($correctOptionIds, $submittedOptionIds))) {
                        $isCorrect = true;
                    }
                } elseif ($type === ExerciseTypeEnum::MATCHING) {
                    $totalGradableQuestions++;
                    $submittedPairs = (array) $answerData['answer_data'];
                    $allMatched = true;
                    foreach ($submittedPairs as $pair) {
                        if (($pair['left_option_id'] ?? null) !== ($pair['right_option_id'] ?? null)) {
                            $allMatched = false;
                            break;
                        }
                    }
                    if ($allMatched && count($submittedPairs) === $question->options->count()) {
                        $isCorrect = true;
                    }
                } elseif ($type === ExerciseTypeEnum::ORDERING) {
                    $totalGradableQuestions++;
                    $correctOrderIds = $question->options->sortBy('order')->pluck('id')->toArray();
                    $submittedOrderIds = (array) $answerData['answer_data'];
                    if ($correctOrderIds === $submittedOrderIds) {
                        $isCorrect = true;
                    }
                }

                if ($isCorrect) {
                    $correctAnswersCount++;
                }
            }

            $performanceAccuracy = $totalGradableQuestions > 0 ? ($correctAnswersCount / $totalGradableQuestions) * 100 : 0;

            // Create submission
            $submission = $assessment->submissions()->create([
                'child_id' => $child->id,
                'assessment_version' => $assessment->version,
                'status' => 'pending_review',
                'attempt_number' => $attemptNumber,
                'performance_accuracy' => $performanceAccuracy,
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
