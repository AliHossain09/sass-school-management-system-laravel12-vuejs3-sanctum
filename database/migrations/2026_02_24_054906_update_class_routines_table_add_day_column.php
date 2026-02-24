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
            // নতুন column add
            $table->enum('day', [
                'Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'
            ])->after('teacher_id');

            // পুরনো other_days column drop
            $table->dropColumn('other_days');
        });
    }

    public function down(): void
    {
        Schema::table('class_routines', function (Blueprint $table) {
            // rollback করার জন্য column recreate
            $table->json('other_days')->nullable()->after('is_break');
            $table->dropColumn('day');
        });
    }
};
