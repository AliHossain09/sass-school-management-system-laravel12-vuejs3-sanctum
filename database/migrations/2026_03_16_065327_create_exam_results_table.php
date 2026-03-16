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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_id')->constrained('examinations')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->string('academic_year')->nullable();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            
            $table->decimal('grand_total', 8, 2);
            $table->decimal('percent', 5, 2);
            $table->string('grade', 10);
            $table->string('result', 50); // Pass/Fail

            $table->dateTime('published_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Prevent duplicating results for same exam, student and class
            $table->unique(['examination_id', 'class_id', 'student_id', 'academic_year'], 'exam_result_unique_row');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
