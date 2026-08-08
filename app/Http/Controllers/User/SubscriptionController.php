<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use App\Models\Subscription;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use ApiResponseTrait;

    public function gateway(Request $request)
    {
        $user = auth('sanctum')->user();

        // Fetch all active subscription packages
        $packages = SubscriptionPackage::where('is_active', true)->get();

        // Fetch user's active subscriptions
        // A user might have multiple active subscriptions if they have multiple children,
        // or a single subscription covering the account.
        $activeSubscriptions = Subscription::where('user_id', $user->id)
            ->where('status', 'active') // Assuming 'active' is the status string for active subscriptions
            ->where('end_date', '>', now())
            ->get();

        return $this->successResponse(__('Subscription Gateway Data'), [
            'packages' => $packages,
            'active_subscriptions' => $activeSubscriptions,
        ]);
    }
}
