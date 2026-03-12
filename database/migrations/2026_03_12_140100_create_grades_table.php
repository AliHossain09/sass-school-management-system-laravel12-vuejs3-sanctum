<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            $table->string('grade'); // A+, A, B...
            $table->decimal('grade_point', 4, 2);
            $table->unsignedTinyInteger('mark_from');
            $table->unsignedTinyInteger('mark_upto');

            $table->timestamps();

            $table->unique(['school_id', 'grade']);
            $table->index(['school_id', 'mark_from', 'mark_upto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};

