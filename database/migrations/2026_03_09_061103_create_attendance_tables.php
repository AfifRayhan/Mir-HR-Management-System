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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('device_logs', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 50);
            $table->dateTime('punch_time');
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->decimal('working_hours', 5, 2)->default(0);
            $table->integer('late_minutes')->default(0);
            $table->enum('status', ['present', 'late', 'absent', 'leave'])->default('present');
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        Schema::create('manual_attendance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_attendance_adjustments');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('device_logs');
        Schema::dropIfExists('devices');
    }
};
