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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id'); // per school
            $table->string('title');
            $table->enum('type', ['all', 'class']); // all class or specific class
            $table->date('publish_date');

            $table->json('class_ids')->nullable(); // multiple classes
            $table->json('section_ids')->nullable(); // multiple sections, nullable

            $table->text('description')->nullable();
            
            $table->timestamps();

            $table->foreign('school_id')
                ->references('id')->on('schools')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
