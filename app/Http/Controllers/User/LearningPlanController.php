<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class LearningPlanController extends Controller
{
    use ApiResponseTrait;

    public function showProgressTree(Request $request, Child $child)
    {
        // Authorize
        if ($child->parent_id !== auth('sanctum')->id()) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        // Fetch the active learning plan assignments for this child
        $progressTree = ChildLearningPlan::where('child_id', $child->id)
            ->where('is_completed', false)
            ->with([
                'learningPlan.learningGoals.learningLessons.learningExercises'
            ])
            ->first();

        if (!$progressTree) {
            return $this->successResponse(__('No active learning plan found.'), null);
        }

        // Return the full progress tree.
        // The mobile client will parse the `is_locked` states on Goals/Lessons to determine 
        // if they should be rendered as Asynchronous (free choice) or Sequential (rigid pipeline).
        return $this->successResponse(__('Learning plan progress tree retrieved successfully.'), [
            'progress_tree' => $progressTree,
        ]);
    }
}
