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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_code')->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            // Basic
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender',['male','female','other']);
            $table->date('dob')->nullable();
            $table->string('photo')->nullable();
            $table->string('nid')->nullable();

            // Professional
            $table->json('subjects')->nullable();
            $table->string('class_assigned')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('grade')->nullable();
            $table->enum('employment_type',['full-time','part-time'])->default('full-time');
            $table->string('department')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // Other
            $table->string('emergency_contact')->nullable();
            $table->string('qualification')->nullable();
            $table->integer('experience')->nullable();
            $table->decimal('salary',10,2)->nullable();

            $table->timestamps();
                    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
