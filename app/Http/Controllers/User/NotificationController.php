<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all notifications for the authenticated user (paginated).
     */
    public function index(Request $request)
    {
        $user = auth('user')->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        $mapped = $notifications->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'data' => $notification->data,
                'is_read' => ! is_null($notification->read_at),
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at->toIso8601String(),
            ];
        });

        return $this->successResponse(__('Notifications retrieved successfully.'), [
            'notifications' => $mapped,
            'unread_count' => $user->unreadNotifications()->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id)
    {
        $user = auth('user')->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $notification->markAsRead();

        return $this->successResponse(__('Notification marked as read.'));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $user = auth('user')->user();

        $user->unreadNotifications()->update(['read_at' => now()]);

        return $this->successResponse(__('All notifications marked as read.'));
    }

    /**
     * Delete a single notification.
     */
    public function destroy(string $id)
    {
        $user = auth('user')->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $notification->delete();

        return $this->successResponse(__('Notification deleted successfully.'));
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll()
    {
        $user = auth('user')->user();

        $user->notifications()->delete();

        return $this->successResponse(__('All notifications deleted successfully.'));
    }
}
