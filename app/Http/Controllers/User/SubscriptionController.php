<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use App\Models\Subscription;
use App\Traits\ApiResponseTrait;
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

        return $this->successResponse(__('Subscription Gateway Data'), [
            'packages' => $packages,
            'active_subscriptions' => $activeSubscriptions,
        ]);
    }
}
