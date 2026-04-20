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
        Schema::create('roster_times', function (Blueprint $table) {
            $table->id();
            $table->string('group_slug'); // e.g., tech, noc-borak, noc-sylhet
            $table->string('shift_key');  // e.g., A, Technician A
            $table->string('display_label'); // e.g., Shift A, General
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('badge_class')->default('badge-off');
            $table->boolean('is_off_day')->default(false);
            $table->timestamps();

            $table->unique(['group_slug', 'shift_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_times');
    }
};
