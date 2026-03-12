<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('allowed_days')->nullable()->after('name');
            $table->enum('applicable_to', ['all', 'teacher', 'student'])->default('all')->after('allowed_days');
            $table->enum('applicable_gender', ['any', 'male', 'female', 'other'])->default('any')->after('applicable_to');

            $table->index(['school_id', 'is_active', 'applicable_to']);
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'is_active', 'applicable_to']);
            $table->dropColumn(['allowed_days', 'applicable_to', 'applicable_gender']);
        });
    }
};

