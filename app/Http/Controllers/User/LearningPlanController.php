<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class LearningPlanController extends Controller
{
    public function showProgressTree(Request $request, Child $child)
    {
        if ($child->parent_id !== auth('sanctum')->id()) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $progressTree = ChildLearningPlan::where('child_id', $child->id)
            ->where('status', 'active')
            ->with([
                'learningPlan.learningGoals.learningLessons.learningExercises'
            ])
            ->first();

        if (!$progressTree) {
            return $this->successResponse(__('No active learning plan found.'), null);
        }

        return $this->successResponse(__('Learning plan progress tree retrieved successfully.'), [
            'progress_tree' => $progressTree,
        ]);
    }
}
