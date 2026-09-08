<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationTopicController extends Controller
{
    use ApiResponseTrait;

    /**
     * Subscribe a device token to a specific FCM topic.
     * Called when the user enables a notification toggle.
     *
     * Available topics: daily_sessions, progress_reports, plan_updates
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
            'topic' => 'required|string|in:daily_sessions,progress_reports,plan_updates',
        ]);

        $success = fcm_subscribe($validated['device_token'], $validated['topic']);

        if (! $success) {
            return $this->failedResponse(__('Failed to subscribe to topic.'), [], 500);
        }

        return $this->successResponse(__('Subscribed successfully.'));
    }

    /**
     * Unsubscribe a device token from a specific FCM topic.
     * Called when the user disables a notification toggle.
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
            'topic' => 'required|string|in:daily_sessions,progress_reports,plan_updates',
        ]);

        $success = fcm_unsubscribe($validated['device_token'], $validated['topic']);

        if (! $success) {
            return $this->failedResponse(__('Failed to unsubscribe from topic.'), [], 500);
        }

        return $this->successResponse(__('Unsubscribed successfully.'));
    }
}
