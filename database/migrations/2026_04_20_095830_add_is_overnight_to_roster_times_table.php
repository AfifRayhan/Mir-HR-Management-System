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
        Schema::table('roster_times', function (Blueprint $table) {
            $table->boolean('is_overnight')->default(false)->after('is_off_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_times', function (Blueprint $table) {
            $table->dropColumn('is_overnight');
        });
    }
};
