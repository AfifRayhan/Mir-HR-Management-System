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
        Schema::table('holidays', function (Blueprint $table) {
            // Adding a unique index to prevent duplicate holiday entries
            // We include title, from_date and office_id to allow the same holiday name/date for different offices if needed.
            $table->unique(['title', 'from_date', 'office_id'], 'holiday_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropUnique('holiday_unique_idx');
        });
    }
};
