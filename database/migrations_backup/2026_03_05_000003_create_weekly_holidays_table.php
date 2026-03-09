<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('day_name'); // Monday, Tuesday, etc.
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_holidays');
    }
};
