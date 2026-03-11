<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Notifications\EventCreatedNotification;
use Illuminate\Support\Facades\Notification;

class EventService
{
    public function paginate(?int $schoolId, string $search = '', int $perPage = 10)
    {
        return Event::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->orderBy('start_date', 'desc')
            ->paginate($perPage);
    }

    public function calendarEvents(int $schoolId)
    {
        return Event::where('school_id', $schoolId)
            ->select('id', 'title', 'start_date', 'end_date')
            ->get();
    }

    public function store(User $actor, array $data): Event
    {
        $event = Event::create([
            'title' => $data['title'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'school_id' => $actor->school_id,
        ]);

        $recipients = User::where('school_id', $actor->school_id)
            ->whereIn('role', ['teacher', 'student'])
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new EventCreatedNotification($event));
        }

        return $event;
    }

    public function update(User $actor, Event $event, array $data): ?Event
    {
        if ($event->school_id !== $actor->school_id) {
            return null;
        }

        $event->update(array_filter([
            'title' => $data['title'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ], fn ($v) => ! is_null($v)));

        return $event;
    }

    public function destroy(User $actor, Event $event): bool
    {
        if ($event->school_id !== $actor->school_id) {
            return false;
        }

        $event->delete();

        return true;
    }
}

