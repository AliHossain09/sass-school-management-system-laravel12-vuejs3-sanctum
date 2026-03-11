<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null)
    {
        $query = Student::with(['schoolClass', 'section', 'electiveSubject'])
            ->where('school_id', $actor->school_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function store(User $actor, array $data, UploadedFile $photo): Student
    {
        $photoPath = $photo->store('students', 'public');

        try {
            return DB::transaction(function () use ($actor, $data, $photoPath) {
                $hashedPassword = Hash::make($data['password']);

                $user = User::create([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'email' => $data['email'],
                    'password' => $hashedPassword,
                    'role' => 'student',
                    'school_id' => $actor->school_id,
                ]);

                return Student::create([
                    'student_code' => $data['student_code'],
                    'user_id' => $user->id,
                    'school_id' => $actor->school_id,

                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'dob' => $data['dob'],
                    'gender' => $data['gender'],
                    'religion' => $data['religion'],
                    'nationality' => $data['nationality'] ?? null,
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'photo' => $photoPath,
                    'extra_curricular' => $data['extra_curricular'] ?? null,
                    'description' => $data['description'] ?? null,

                    'father_name' => $data['father_name'] ?? null,
                    'father_phone' => $data['father_phone'] ?? null,
                    'mother_name' => $data['mother_name'] ?? null,
                    'mother_phone' => $data['mother_phone'] ?? null,

                    'local_guardian_name' => $data['local_guardian_name'] ?? null,
                    'local_guardian_phone' => $data['local_guardian_phone'] ?? null,
                    'local_guardian_relationship' => $data['local_guardian_relationship'] ?? null,

                    'present_address' => $data['present_address'] ?? null,
                    'permanent_address' => $data['permanent_address'] ?? null,

                    'class_id' => $data['class_id'],
                    'section_id' => $data['section_id'] ?? null,
                    'academic_year' => $data['academic_year'],
                    'shift' => $data['shift'] ?? null,
                    'id_card_number' => $data['id_card_number'] ?? null,
                    'roll_number' => $data['roll_number'] ?? null,
                    'board_registration_number' => $data['board_registration_number'] ?? null,
                    'elective_subject_id' => $data['elective_subject_id'] ?? null,

                    'username' => $data['username'],
                    'password' => $hashedPassword,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($photoPath);
            throw $e;
        }
    }

    public function update(User $actor, Student $student, array $data, ?UploadedFile $photo = null): ?Student
    {
        if ($student->school_id !== $actor->school_id) {
            return null;
        }

        return DB::transaction(function () use ($student, $data, $photo) {
            if ($photo) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $student->photo = $photo->store('students', 'public');
            }

            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $student->fill(array_filter(
                $data,
                fn ($value) => ! is_null($value)
            ));
            $student->save();

            if ($student->user) {
                $name = trim(($data['first_name'] ?? $student->first_name).' '.($data['last_name'] ?? $student->last_name));

                $student->user->fill(array_filter([
                    'name' => $name,
                    'email' => $data['email'] ?? null,
                    'password' => $data['password'] ?? null,
                ], fn ($v) => ! is_null($v)));
                $student->user->save();
            }

            return $student;
        });
    }

    public function destroy(User $actor, Student $student): bool
    {
        if ($student->school_id !== $actor->school_id) {
            return false;
        }

        return (bool) DB::transaction(function () use ($student) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }

            $student->user?->delete();
            $student->delete();

            return true;
        });
    }
}

