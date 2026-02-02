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

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            // Personal Info
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob');
            $table->enum('gender', ['male','female','other']);
            $table->string('religion');
            $table->string('nationality')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable(); // student photo
            $table->text('extra_curricular')->nullable();
            $table->text('description')->nullable();

            // Guardian Info
            $table->string('father_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();

            $table->string('local_guardian_name')->nullable();
            $table->string('local_guardian_phone')->nullable();
            $table->string('local_guardian_relationship')->nullable();

            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();

            // Academic Info
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('section_id')->nullable()->constrained('sections');
            $table->string('academic_year');

            $table->string('shift')->nullable();
            $table->string('id_card_number')->nullable();
            $table->string('roll_number')->nullable();
            $table->string('board_registration_number')->nullable();

            $table->foreignId('elective_subject_id')->nullable()
                ->constrained('subjects')->nullOnDelete();

            // Access Info
            $table->string('username')->unique()->nullable();
            $table->string('password')->nullable();

            // Access Info (Student Table)
            $table->string('username')->unique();
            $table->string('password');

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
