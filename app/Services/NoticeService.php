<?php

namespace App\Services;

use App\Models\Notice;
use App\Models\User;

class NoticeService
{
    public function paginateForUser(User $user, ?string $search = null, int $perPage = 10)
    {
        $query = Notice::query()->where('school_id', $user->school_id);

        if ($user->role === 'student') {
            $student = $user->student;

            $query->where(function ($q) use ($student) {
                $q->where('type', 'all')
                    ->orWhereJsonContains('class_ids', $student?->class_id)
                    ->orWhereJsonContains('section_ids', $student?->section_id);
            });
        }

        if ($user->role === 'teacher') {
            $query->where(function ($q) {
                $q->where('type', 'all')
                    ->orWhereNotNull('class_ids');
            });
        }

        if ($search !== null && $search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query->orderBy('publish_date', 'desc')->paginate($perPage);
    }

    public function store(User $user, array $data): Notice
    {
        return Notice::create([
            'school_id' => $user->school_id,
            'title' => $data['title'],
            'type' => $data['type'],
            'publish_date' => $data['publish_date'],
            'class_ids' => $data['class_ids'] ?? null,
            'section_ids' => $data['section_ids'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(User $user, Notice $notice, array $data): ?Notice
    {
        if ($notice->school_id !== $user->school_id) {
            return null;
        }

        $notice->fill(array_filter([
            'title' => $data['title'] ?? null,
            'type' => $data['type'] ?? null,
            'publish_date' => $data['publish_date'] ?? null,
            'class_ids' => $data['class_ids'] ?? null,
            'section_ids' => $data['section_ids'] ?? null,
            'description' => $data['description'] ?? null,
        ], fn ($v) => ! is_null($v)));
        $notice->save();

        return $notice;
    }

    public function destroy(User $user, Notice $notice): bool
    {
        if ($notice->school_id !== $user->school_id) {
            return false;
        }

        $notice->delete();

        return true;
    }
}

