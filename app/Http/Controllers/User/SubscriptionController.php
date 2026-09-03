<?php

namespace App\Http\Controllers\User;

use App\Enums\BillingCycleEnum;
use App\Enums\ChildLearningPlanStatusEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SubscribeRequest;
use App\Http\Resources\User\SubscriptionPackageResource;
use App\Http\Resources\User\SubscriptionResource;
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
                    'severity_level' => $plan->autism_level,
                ];
            }
        }

        return $this->successResponse(__('Subscription Gateway Data'), [
            'packages' => SubscriptionPackageResource::collection($packages),
            'active_subscriptions' => SubscriptionResource::collection($activeSubscriptions),
            'pending_plan_summary' => $pendingPlanSummary,
        ]);
    }

    public function subscribe(SubscribeRequest $request)
    {
        $user = auth('sanctum')->user();

        $activeSubscription = Subscription::where('parent_id', $user->id)
            ->where('status', SubscriptionStatusEnum::ACTIVE->value)
            ->where('expiry_date', '>', now())
            ->first();

        if ($activeSubscription) {
            return $this->failedResponse(__('You already have an active subscription.'), 400);
        }

        $package = SubscriptionPackage::findOrFail($request->package_id);

        $startDate = now();
        $expiryDate = match ($package->billing_cycle) {
            BillingCycleEnum::Monthly => now()->addMonth(),
            BillingCycleEnum::Quarterly => now()->addMonths(3),
            BillingCycleEnum::SemiAnnually => now()->addMonths(6),
            BillingCycleEnum::Annually => now()->addYear(),
            default => now()->addMonth(),
        };

        $subscription = Subscription::create([
            'parent_id' => $user->id,
            'subscription_package_id' => $package->id,
            'amount_paid' => $package->price,
            'status' => SubscriptionStatusEnum::ACTIVE->value,
            'start_date' => $startDate,
            'expiry_date' => $expiryDate,
            'is_free' => $package->price <= 0,
        ]);

        return $this->successResponse(__('Subscribed successfully'), [
            'subscription' => new SubscriptionResource($subscription),
        ]);
    }
}
