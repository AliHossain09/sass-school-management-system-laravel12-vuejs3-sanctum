<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\User;

class SchoolClassService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null)
    {
        $query = SchoolClass::query()->where('school_id', $actor->school_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    public function store(User $actor, array $data): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $actor->school_id,
            'name' => $data['name'],
            'order' => $data['order'] ?? null,
            'group' => $data['group'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(User $actor, SchoolClass $schoolClass, array $data): ?SchoolClass
    {
        if ($schoolClass->school_id !== $actor->school_id) {
            return null;
        }

        $schoolClass->fill(array_filter(
            $data,
            fn ($value) => ! is_null($value)
        ));
        $schoolClass->save();

        return $schoolClass;
    }

    public function destroy(User $actor, SchoolClass $schoolClass): bool
    {
        if ($schoolClass->school_id !== $actor->school_id) {
            return false;
        }

        $schoolClass->delete();

        return true;
    }
}

