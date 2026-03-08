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
        Schema::table('class_routines', function (Blueprint $table) {
            if (! Schema::hasColumn('class_routines', 'day')) {
                $table->enum('day', [
                    'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
                ])->after('teacher_id');
            }

            if (Schema::hasColumn('class_routines', 'other_days')) {
                $table->dropColumn('other_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_routines', function (Blueprint $table) {
            if (! Schema::hasColumn('class_routines', 'other_days')) {
                $table->json('other_days')->nullable()->after('is_break');
            }

            if (Schema::hasColumn('class_routines', 'day')) {
                $table->dropColumn('day');
            }
        });
    }
};
