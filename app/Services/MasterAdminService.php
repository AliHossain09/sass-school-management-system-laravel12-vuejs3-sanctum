<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterAdminService
{
    public function paginateSchools(int $perPage = 10)
    {
        return School::with('headmaster')->paginate($perPage);
    }

    public function createSchool(array $data): School
    {
        return DB::transaction(function () use ($data) {
            $school = School::create([
                'name' => $data['name'],
                'address' => $data['address'],
            ]);

            $headmaster = User::create([
                'name' => $data['name'].' Headmaster',
                'email' => strtolower(str_replace(' ', '', $data['name'])).rand(1000, 9999).'@school.com',
                'password' => Hash::make($data['password']),
                'role' => 'headmaster',
                'school_id' => $school->id,
            ]);

            $school->update(['headmaster_id' => $headmaster->id]);

            AcademicYear::create([
                'school_id' => $school->id,
                'start_date' => Carbon::now()->startOfYear(),
                'end_date' => Carbon::now()->endOfYear(),
                'is_active' => true,
            ]);

            return $school;
        });
    }

    public function updateSchool(School $school, array $data): School
    {
        $school->update([
            'name' => $data['name'],
            'address' => $data['address'],
        ]);

        return $school;
    }

    public function deleteSchool(School $school): void
    {
        DB::transaction(function () use ($school) {
            User::where('school_id', $school->id)->delete();
            $school->delete();
        });
    }
}

