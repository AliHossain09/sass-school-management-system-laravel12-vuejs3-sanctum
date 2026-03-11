<?php

namespace App\Services;

use App\Models\Section;
use App\Models\User;

class SectionService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null)
    {
        $query = Section::with('schoolClass')
            ->where('school_id', $actor->school_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    public function store(User $actor, array $data): Section
    {
        return Section::create([
            'name' => $data['name'],
            'class_id' => $data['class_id'],
            'description' => $data['description'] ?? null,
            'school_id' => $actor->school_id,
        ]);
    }

    public function update(User $actor, Section $section, array $data): ?Section
    {
        if ($section->school_id !== $actor->school_id) {
            return null;
        }

        $section->fill(array_filter([
            'name' => $data['name'] ?? null,
            'class_id' => $data['class_id'] ?? null,
            'description' => $data['description'] ?? null,
        ], fn ($v) => ! is_null($v)));
        $section->save();

        return $section;
    }

    public function destroy(User $actor, Section $section): bool
    {
        if ($section->school_id !== $actor->school_id) {
            return false;
        }

        $section->delete();

        return true;
    }
}

