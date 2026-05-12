<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure all existing records are migrated to inactive
        DB::table('employees')
            ->whereIn('status', ['left', 'hold'])
            ->update(['status' => 'inactive']);

        // Then modify the enum column
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'left', 'hold'])->default('active')->change();
        });
    }
};
