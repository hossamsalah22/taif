<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExerciseInteractionLog;
use App\Models\ChildLearningPlan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ExerciseInteractionController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'learning_exercise_id' => 'required|exists:learning_exercises,id',
            'is_successful' => 'required|boolean',
            'duration_seconds' => 'required|integer|min:0',
            'trials_count' => 'required|integer|min:1',
            'interaction_type' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user();

        // Authorize child belongs to user
        if (!$user->children()->where('id', $validated['child_id'])->exists()) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        // Log the interaction
        $interaction = ExerciseInteractionLog::create([
            'child_id' => $validated['child_id'],
            'learning_exercise_id' => $validated['learning_exercise_id'],
            'is_successful' => $validated['is_successful'],
            'duration_seconds' => $validated['duration_seconds'],
            'trials_count' => $validated['trials_count'],
            'interaction_type' => $validated['interaction_type'],
        ]);

        // If successful, update the ChildLearningPlan progress (unlocking the next sequential node)
        // In a real application, this would invoke a complex service to evaluate node completion,
        // calculate thresholds, and issue rewards.
        if ($validated['is_successful']) {
            // Simplified: We would mark the exercise as complete in a progress ledger table.
            // ...
        }

        return $this->successResponse(__('Exercise interaction logged successfully.'), [
            'interaction_id' => $interaction->id
        ], 201);
    }
}
