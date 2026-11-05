<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SubscribeRequest;
use App\Http\Resources\User\PaymentResource;
use App\Http\Resources\User\SubscriptionPackageResource;
use App\Http\Resources\User\SubscriptionResource;
use App\Models\ChildLearningPlan;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Services\SubscriptionCheckout;
use Illuminate\Http\Request;
use Throwable;

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

    public function subscribe(SubscribeRequest $request, SubscriptionCheckout $checkout)
    {
        $user = $request->user();

        $activeSubscription = Subscription::where('parent_id', $user->id)
            ->where('status', SubscriptionStatusEnum::ACTIVE->value)
            ->where('expiry_date', '>', now())
            ->first();

        if ($activeSubscription) {
            return $this->failedResponse(__('You already have an active subscription.'), 400);
        }

        $package = SubscriptionPackage::where('is_active', true)->findOrFail($request->package_id);

        if ((float) $package->price <= 0) {
            $subscription = $checkout->activateFree($user, $package);

            return $this->successResponse(__('Subscribed successfully'), [
                'requires_payment' => false,
                'subscription' => new SubscriptionResource($subscription),
            ]);
        }

        try {
            $payment = $checkout->startPayment($user, $package);
        } catch (Throwable) {
            return $this->failedResponse(__('Payment could not be started, please try again later.'), [], 502);
        }

        return $this->successResponse(__('Complete the payment to activate your subscription'), [
            'requires_payment' => true,
            'payment' => new PaymentResource($payment),
        ]);
    }
}
