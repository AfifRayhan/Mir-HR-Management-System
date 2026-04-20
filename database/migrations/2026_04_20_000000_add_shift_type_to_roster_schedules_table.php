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
        Schema::table('roster_schedules', function (Blueprint $table) {
            // Add shift_type column after date
            $table->string('shift_type')->nullable()->after('date');
            
            // Add created_by column (foreign key to users)
            $table->foreignId('created_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            
            // Make office_time_id nullable as we shift to dynamic labels
            $table->unsignedBigInteger('office_time_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_schedules', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['shift_type', 'created_by']);
            $table->unsignedBigInteger('office_time_id')->nullable(false)->change();
        });
    }
};
