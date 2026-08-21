<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\ChildLearningPlan;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function gateway(Request $request)
    {
        $user = auth('sanctum')->user();

        $packages = SubscriptionPackage::where('is_active', true)->get();

        $activeSubscriptions = Subscription::where('parent_id', $user->id)
            ->where('status', 'active')
            ->where('expiry_date', '>', now())
            ->get();

        $children = $user->children()->get();
        $activeChildId = $request->header('X-Active-Child-Id', $children->first()?->id);
        $activeChild = $activeChildId ? $children->firstWhere('id', $activeChildId) : null;

        $pendingPlanSummary = null;
        if ($activeChild) {
            $childLearningPlan = ChildLearningPlan::where('child_id', $activeChild->id)
                ->whereIn('status', [ChildLearningPlanStatusEnum::InProgress, ChildLearningPlanStatusEnum::Completed])
                ->with('learningPlan.goals')
                ->latest()
                ->first();

            if ($childLearningPlan && $childLearningPlan->learningPlan) {
                $plan = $childLearningPlan->learningPlan;
                $pendingPlanSummary = [
                    'name' => $plan->name,
                    'weekly_sessions_count' => $plan->weekly_sessions_count,
                    'phase_duration' => $plan->phase_duration,
                    'total_goals' => $plan->goals->count(),
                    'severity_level' => $plan->severity_level,
                ];
            }
        }

        return $this->successResponse(__('Subscription Gateway Data'), [
            'packages' => $packages,
            'active_subscriptions' => $activeSubscriptions,
            'pending_plan_summary' => $pendingPlanSummary,
        ]);
    }
}
