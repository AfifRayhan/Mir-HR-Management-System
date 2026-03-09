<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->unsignedInteger('max_consecutive_days')->nullable()->after('total_days_per_year')->comment('Max consecutive days allowed per request (null = unlimited)');
            $table->unsignedInteger('sort_order')->default(99)->after('carry_forward')->comment('Priority order: lower number = higher priority');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['max_consecutive_days', 'sort_order']);
        });
    }
};
