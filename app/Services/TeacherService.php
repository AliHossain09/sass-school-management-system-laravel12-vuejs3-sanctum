<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null)
    {
        $query = Teacher::query()->where('school_id', $actor->school_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function store(User $actor, array $data, ?UploadedFile $photo = null): Teacher
    {
        $photoPath = null;
        if ($photo) {
            $photoPath = $photo->store('teachers', 'public');
        }

        try {
            return DB::transaction(function () use ($actor, $data, $photoPath) {
                $user = User::create([
                    'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'school_id' => $actor->school_id,
                ]);

                return Teacher::create([
                    'teacher_code' => 'T-'.strtoupper(Str::random(6)),
                    'user_id' => $user->id,
                    'school_id' => $actor->school_id,

                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'gender' => $data['gender'],
                    'dob' => $data['dob'] ?? null,

                    'nid' => $data['nid'] ?? null,
                    'subjects' => $data['subjects'] ?? null,
                    'class_assigned' => $data['class_assigned'] ?? null,
                    'joining_date' => $data['joining_date'] ?? null,
                    'grade' => $data['grade'] ?? null,
                    'employment_type' => $data['employment_type'] ?? 'full-time',
                    'department' => $data['department'] ?? null,

                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'address' => $data['address'] ?? null,

                    'emergency_contact' => $data['emergency_contact'] ?? null,
                    'qualification' => $data['qualification'] ?? null,
                    'experience' => $data['experience'] ?? null,
                    'salary' => $data['salary'] ?? null,

                    'photo' => $photoPath,
                ]);
            });
        } catch (\Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $e;
        }
    }

    public function update(User $actor, Teacher $teacher, array $data, ?UploadedFile $photo = null): ?Teacher
    {
        if ($teacher->school_id !== $actor->school_id) {
            return null;
        }

        if ($photo) {
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }
            $teacher->photo = $photo->store('teachers', 'public');
        }

        $teacher->fill(array_filter(
            $data,
            fn ($value) => ! is_null($value)
        ));
        $teacher->save();

        if ($teacher->user) {
            $name = null;
            if (! empty($data['first_name']) || ! empty($data['last_name'])) {
                $first = $data['first_name'] ?? $teacher->first_name;
                $last = $data['last_name'] ?? $teacher->last_name;
                $name = trim($first.' '.$last);
            }

            $teacher->user->fill(array_filter([
                'name' => $name,
                'email' => $data['email'] ?? null,
            ], fn ($v) => ! is_null($v)));
            $teacher->user->save();
        }

        return $teacher;
    }

    public function destroy(User $actor, Teacher $teacher): bool
    {
        if ($teacher->school_id !== $actor->school_id) {
            return false;
        }

        if ($teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
        }

        $teacher->user?->delete();

        return true;
    }
}

