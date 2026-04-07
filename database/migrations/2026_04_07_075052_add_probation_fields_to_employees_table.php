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
            $table->string('employee_type')->default('Regular')->after('status');
            $table->integer('probation_duration')->nullable()->after('employee_type');
            $table->date('probation_start_date')->nullable()->after('probation_duration');
            $table->date('probation_end_date')->nullable()->after('probation_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['employee_type', 'probation_duration', 'probation_start_date', 'probation_end_date']);
        });
    }
};
