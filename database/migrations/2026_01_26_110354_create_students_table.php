<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_code')->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            // Personal
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender',['male','female','other']);
            $table->date('dob')->nullable();
            $table->string('photo')->nullable();
            $table->string('nid_birth')->nullable();

            // Academic
            $table->string('class');
            $table->string('section')->nullable();
            $table->string('roll_number')->nullable();
            $table->string('academic_year');
            $table->date('admission_date')->nullable();
            $table->string('previous_school')->nullable();

            // Guardian
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();
            $table->text('address')->nullable();

            // Additional
            $table->string('religion')->nullable();
            $table->string('nationality')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->string('transport_route')->nullable();
            $table->enum('fee_status',['paid','due','partial'])->default('due');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
