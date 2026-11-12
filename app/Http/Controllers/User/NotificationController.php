<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSubmission;
use App\Notifications\ReportPublishedNotification;
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
                'data' => $this->withReportLink($notification->type, $this->translateData($notification->data)),
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

    /**
     * Translate the stored notification message into the current locale.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function translateData(array $data): array
    {
        if (isset($data['message_key'])) {
            $data['message'] = __($data['message_key'], $data['message_params'] ?? []);
        } elseif (isset($data['message']) && is_string($data['message'])) {
            $data['message'] = __($data['message']);
        }

        unset($data['message_key'], $data['message_params']);

        return $data;
    }

    /**
     * Give report notifications stored before the link existed the child and report link the app needs to open them.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withReportLink(string $type, array $data): array
    {
        if ($type !== ReportPublishedNotification::class || isset($data['report_url']) || ! isset($data['submission_id'])) {
            return $data;
        }

        $childId = $data['child_id'] ?? AssessmentSubmission::query()->whereKey($data['submission_id'])->value('child_id');

        if ($childId === null) {
            return $data;
        }

        $data['child_id'] = $childId;
        $data['report_url'] = ReportPublishedNotification::reportUrl($childId, $data['submission_id']);

        return $data;
    }
}
