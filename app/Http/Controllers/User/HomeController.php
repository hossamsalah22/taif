<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChildResource;
use App\Models\Child;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('user')->user();

        $children = $user->children()->get();

        $activeChildId = $request->header(
            'X-Active-Child-Id',
            $children->first()?->id
        );

        $activeChild = $activeChildId
            ? $children->firstWhere('id', $activeChildId)
            : null;

        $planProgress = null;

        if ($activeChild) {
            $planProgress = DB::table('child_learning_plans')
                ->where('child_id', $activeChild->id)
                ->where(
                    'status',
                    ChildLearningPlanStatusEnum::InProgress->value
                )
                ->orderBy('created_at', 'asc')
                ->first();
        }

        $recentNotifications = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', $user->getMorphClass())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse(__('Home data retrieved successfully'),[
                'children' => ChildResource::collection($children),
                'active_child' => $activeChild
                    ? ChildResource::make($activeChild)
                    : null,

                'current_plan_progress' => $planProgress,

                'recent_notifications' => $recentNotifications,
            ]
        );
    }
}
