<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a new notification.
     */
    /**
     * Create a new notification.
     */
    public function create(
        int $userId,
        string $type,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Get notifications for a specific user.
     */
    public function getForUser(
        int $userId,
        int $limit = 10,
        bool $unreadOnly = false
    ): Collection {
        $query = Notification::forUser($userId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get all notifications for a user with pagination.
     */
    public function getAllForUser(int $userId, int $perPage = 20)
    {
        return Notification::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (! $notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->count();
    }

    /**
     * Delete a notification.
     */
    public function delete(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (! $notification) {
            return false;
        }

        return $notification->delete();
    }

    /**
     * Delete all notifications for a user.
     */
    public function deleteAllForUser(int $userId): int
    {
        return Notification::forUser($userId)->delete();
    }

    /**
     * Create a success notification.
     */
    public function success(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'success', $title, $description, $data, $actionUrl);
    }

    /**
     * Create an info notification.
     */
    public function info(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'info', $title, $description, $data, $actionUrl);
    }

    /**
     * Create a warning notification.
     */
    public function warning(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'warning', $title, $description, $data, $actionUrl);
    }

    /**
     * Create an error notification.
     */
    public function error(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'error', $title, $description, $data, $actionUrl);
    }
}
