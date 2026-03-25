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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->dateTime('punch_time')->nullable();
            $table->integer('status')->nullable();
            $table->string('device_name', 100)->nullable();
            $table->integer('machine_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'punch_time', 'machine_id'], 'unique_punch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
