<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function listForSchool(User $user)
    {
        return AcademicYear::where('school_id', $user->school_id)
            ->orderByDesc('start_date')
            ->get();
    }

    public function store(User $user, array $data): AcademicYear
    {
        return DB::transaction(function () use ($user, $data) {
            AcademicYear::where('school_id', $user->school_id)->update(['is_active' => false]);

            return AcademicYear::create([
                'school_id' => $user->school_id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => true,
            ]);
        });
    }

    public function update(User $user, AcademicYear $academicYear, array $data): ?AcademicYear
    {
        if ($academicYear->school_id !== $user->school_id) {
            return null;
        }

        return DB::transaction(function () use ($user, $academicYear, $data) {
            if (! empty($data['is_active'])) {
                AcademicYear::where('school_id', $user->school_id)->update(['is_active' => false]);
            }

            $academicYear->update([
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $data['is_active'],
            ]);

            return $academicYear;
        });
    }

    public function destroy(User $user, AcademicYear $academicYear): bool
    {
        if ($academicYear->school_id !== $user->school_id) {
            return false;
        }

        $academicYear->delete();

        return true;
    }
}

