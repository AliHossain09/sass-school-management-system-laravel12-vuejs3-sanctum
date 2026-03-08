<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Event $event)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'event_created',
            'event' => [
                'id' => $this->event->id,
                'title' => $this->event->title,
                'start_date' => $this->event->start_date,
                'end_date' => $this->event->end_date,
            ],
        ];
    }
}
