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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('ot_start')->nullable();
            $table->time('ot_stop')->nullable();
            $table->decimal('total_ot_hours', 5, 2)->default(0);
            $table->boolean('is_workday_duty_plus_5')->default(false);
            $table->boolean('is_holiday_duty_plus_5')->default(false);
            $table->boolean('is_eid_duty')->default(false);
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
