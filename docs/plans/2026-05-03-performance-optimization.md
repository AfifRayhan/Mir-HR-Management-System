# Performance Optimization Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Provide a comprehensive, top-to-bottom performance optimization pass for the Laravel HRMS application.

**Architecture:** We will focus on the most impactful Laravel performance optimizations: adding necessary database indexes for frequently queried columns, resolving N+1 query problems via eager loading in critical controllers, and setting up an optimization script for route/config/view caching.

**Tech Stack:** Laravel, PHP 8.2, MySQL

---

## User Review Required
Please review the proposed performance optimizations. If you are using a local development environment, caching configurations (like `config:cache`) can sometimes interfere with hot-reloading. The optimization script is intended for staging/production use.

## Open Questions
1. Do you currently have Redis installed and available on your server? If so, we can switch the cache and session drivers to `redis` for an additional massive performance boost.
2. Are there any specific pages (like Employee List or Attendance Report) that feel the slowest to you?

## Proposed Changes

### Task 1: Add Database Indexes for Performance

**Files:**
- `[ ]` Create Migration `database/migrations/YYYY_MM_DD_add_performance_indexes.php`

**Step 1: Write the failing test**
```php
public function test_performance_indexes_exist()
{
    $this->assertTrue(Schema::hasIndex('employees', 'employees_user_id_index'));
}
```

**Step 2: Run test to verify it fails**
Run: `php.exe artisan test --filter test_performance_indexes_exist`
Expected: FAIL

**Step 3: Write minimal implementation**
```php
// Run: php.exe artisan make:migration add_performance_indexes
// In the new migration:
public function up()
{
    Schema::table('employees', function (Blueprint $table) {
        $table->index('user_id');
        $table->index('department_id');
        $table->index('designation_id');
    });
    Schema::table('attendance_records', function (Blueprint $table) {
        $table->index('date');
    });
}
```

**Step 4: Run test to verify it passes**
Run: `php.exe artisan migrate`
Run: `php.exe artisan test --filter test_performance_indexes_exist`
Expected: PASS

**Step 5: Commit**
```bash
git add database/migrations/
git commit -m "perf: add database indexes for frequent queries"
```

### Task 2: Eager Load Relationships in OvertimeController

**Files:**
- `[ ]` Modify Controller `app/Http/Controllers/Personnel/OvertimeController.php`

**Step 1: Write the failing test**
```php
public function test_overtime_index_returns_successful_response()
{
    $user = \App\Models\User::factory()->create();
    $response = $this->actingAs($user)->get('/overtimes');
    $response->assertStatus(200);
}
```

**Step 2: Run test to verify it fails/passes**
Run: `php.exe artisan test --filter test_overtime_index_returns_successful_response`

**Step 3: Write minimal implementation**
```php
// In app/Http/Controllers/Personnel/OvertimeController.php
// Change:
// $query = Employee::where('status', 'active')->whereHas('designation'...
// To:
// $query = Employee::with(['designation', 'department', 'officeTime'])->where('status', 'active')...
```

**Step 4: Run test to verify it passes**
Run: `php.exe artisan test --filter test_overtime_index_returns_successful_response`
Expected: PASS

**Step 5: Commit**
```bash
git add app/Http/Controllers/Personnel/OvertimeController.php
git commit -m "perf: eager load relationships in overtime controller"
```

### Task 3: Laravel Application Caching Setup

**Files:**
- `[ ]` Create optimization script `optimize.bat`

**Step 1: Write the failing test**
(No test needed for a bash script)

**Step 2: Run test to verify it fails**
N/A

**Step 3: Write minimal implementation**
```bat
@echo off
echo "Optimizing Laravel Application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo "Application optimized for production."
```

**Step 4: Run test to verify it passes**
Run: `.\optimize.bat`
Expected: Success messages from artisan

**Step 5: Commit**
```bash
git add optimize.bat
git commit -m "chore: add optimization script for production deployment"
```
