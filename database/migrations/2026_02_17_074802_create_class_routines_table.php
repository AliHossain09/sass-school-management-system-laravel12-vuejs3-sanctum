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
       Schema::create('class_routines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('school_id')->constrained()->cascadeOnDelete();
        $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
        $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
        $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
        $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
        $table->enum('day', [
            'Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'
        ]);
        $table->time('start_time');
        $table->time('end_time');
        $table->boolean('is_break')->default(false);
        $table->string('class_room')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_routines');
    }
};
