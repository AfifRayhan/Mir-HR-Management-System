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
        Schema::table('employees', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('department_id');
            $table->index('designation_id');
        });
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['designation_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
