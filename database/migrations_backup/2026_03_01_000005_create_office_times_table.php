<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_times', function (Blueprint $table) {
            $table->id();
            $table->string('shift_name', 100);
            $table->time('start_time');
            $table->time('end_time');
            $table->time('late_after')->nullable();
            $table->time('absent_after')->nullable();
            $table->time('lunch_start')->nullable();
            $table->time('lunch_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_times');
    }
};
