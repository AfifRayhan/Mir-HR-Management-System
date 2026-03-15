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
        Schema::table('offices', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('email');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->integer('order_sequence')->default(0)->after('description');
        });

        Schema::table('office_times', function (Blueprint $table) {
            $table->string('remarks', 100)->nullable()->after('lunch_end');
        });

        Schema::table('weekly_holidays', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_holidays', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
        });

        Schema::table('office_times', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('order_sequence');
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
