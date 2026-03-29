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
            $table->string('blood_group', 10)->nullable()->after('address');
            $table->string('father_name', 150)->nullable()->after('blood_group');
            $table->string('mother_name', 150)->nullable()->after('father_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['blood_group', 'father_name', 'mother_name']);
        });
    }
};
