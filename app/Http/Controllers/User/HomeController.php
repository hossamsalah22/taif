<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        // 1. Fetch children
        $children = $user->children()->get(['id', 'name', 'age', 'gender', 'autism_level']);

        // Active Child context - assume the first child if not explicitly provided, or passed via header
        $activeChildId = $request->header('X-Active-Child-Id', $children->first()->id ?? null);

        // 2. Fetch Plan Progress for the active child
        $planProgress = null;
        if ($activeChildId) {
            $planProgress = DB::table('child_learning_plans')
                ->where('child_id', $activeChildId)
                ->where('is_completed', false)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        // 3. Fetch recent system notifications
        // Fallback to checking settings if notifications table doesn't have what we need,
        // but assuming there's a notifications table for actual alerts:
        $recentNotifications = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse(__('Home data retrieved successfully'), [
            'children' => $children,
            'active_child_id' => $activeChildId,
            'current_plan_progress' => $planProgress,
            'recent_notifications' => $recentNotifications,
        ]);
    }
}
