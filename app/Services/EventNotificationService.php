<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class EventNotificationService
{
    private const EVENT_CREATED_TYPE = 'App\\Notifications\\EventCreatedNotification';

    public function latestForUser(User $user, int $limit = 20): array
    {
        $notifications = $user->notifications()
            ->where('type', self::EVENT_CREATED_TYPE)
            ->latest()
            ->take($limit)
            ->get()
            ->map(function (DatabaseNotification $notification) {
                $event = $notification->data['event'] ?? null;

                return [
                    'id' => $notification->id,
                    'is_read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'event' => $event,
                ];
            });

        return [
            'data' => $notifications,
            'unread_count' => $notifications->where('is_read', false)->count(),
        ];
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->where('type', self::EVENT_CREATED_TYPE)
            ->first();

        if (! $notification) {
            return false;
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return true;
    }
}

