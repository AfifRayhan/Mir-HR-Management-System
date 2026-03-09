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
        // 1. Essential System Tables
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('employee_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 2. Roles and Permissions (Menu Based)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('route_name')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('role_menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'menu_item_id']);
        });

        // Add role_id foreign key to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        // 3. HR Structure Tables
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('short_name', 50)->nullable();
            $table->unsignedBigInteger('incharge_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('short_name', 50)->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

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

        Schema::create('office_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order_number')->default(0);
            $table->timestamps();
        });

        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('office_type_id')->constrained('office_types')->cascadeOnDelete();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('order_number')->default(0);
            $table->timestamps();
        });

        // 4. Employee Table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('employee_code', 50)->unique()->nullable();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('joining_date')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('office_time_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('status', ['active', 'resigned'])->default('active');
            $table->timestamps();
        });

        // Attach department incharge foreign key
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('incharge_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 5. Holidays and Leave Management
        Schema::create('weekly_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('day_name');
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->year('year');
            $table->string('title');
            $table->boolean('all_office')->default(false);
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('total_days');
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('total_days_per_year');
            $table->unsignedInteger('max_consecutive_days')->nullable()->comment('Max consecutive days allowed per request (null = unlimited)');
            $table->boolean('carry_forward')->default(false);
            $table->unsignedInteger('sort_order')->default(99)->comment('Priority order: lower number = higher priority');
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->year('year');
            $table->integer('opening_balance');
            $table->integer('used_days')->default(0);
            $table->integer('remaining_days');
            $table->timestamps();
        });

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->text('leave_address')->nullable();
            $table->text('remarks')->nullable();
            $table->string('supporting_document', 255)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('weekly_holidays');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('office_types');
        Schema::dropIfExists('office_times');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('sections');
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['incharge_id']);
        });
        Schema::dropIfExists('departments');
        Schema::dropIfExists('role_menu_item');
        Schema::dropIfExists('menu_items');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
        Schema::dropIfExists('roles');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
