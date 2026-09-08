<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateSettingsRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $user = auth('user')->user();

        $notificationsRecord = DB::table(config('settings.repositories.database.table') ?? 'settings')
            ->where('group', 'notifications')
            ->where('name', 'user_'.$user->id)
            ->first();

        $notifications = $notificationsRecord ? json_decode($notificationsRecord->payload, true) : [
            'daily_session' => true,
            'progress_reports' => true,
            'plan_updates' => true,
            'subscription_billing' => true,
        ];

        return $this->successResponse(__('Settings retrieved successfully.'), [
            'notifications' => [
                'daily_session' => $notifications['daily_session'] ?? true,
                'progress_reports' => $notifications['progress_reports'] ?? true,
                'plan_updates' => $notifications['plan_updates'] ?? true,
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        $user = auth('user')->user();

        $data = $request->validated();

        // TAYF-87: Update Language
        if (isset($data['locale'])) {
            $user->update(['locale' => $data['locale']]);
        }

        // TAYF-88: Update Sensory Settings
        if (isset($data['sensory'])) {
            $childId = $data['sensory']['child_id'];
            // Verify child belongs to user
            if ($user->children()->where('id', $childId)->exists()) {
                DB::table(config('settings.repositories.database.table') ?? 'settings')->updateOrInsert(
                    ['group' => 'sensory', 'name' => 'child_'.$childId],
                    ['payload' => json_encode([
                        'audio_volume' => $data['sensory']['audio_volume'],
                        'screen_brightness' => $data['sensory']['screen_brightness'],
                    ]), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        // TAYF-89: Manage Notifications
        if (isset($data['notifications'])) {
            $payload = $data['notifications'];
            // TYF-SET-05: Lock down subscription billing alerts
            $payload['subscription_billing'] = true; // Hardcoded true per requirements

            DB::table(config('settings.repositories.database.table') ?? 'settings')->updateOrInsert(
                ['group' => 'notifications', 'name' => 'user_'.$user->id],
                ['payload' => json_encode($payload), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return $this->successResponse(__('Settings updated successfully.'));
    }
}
