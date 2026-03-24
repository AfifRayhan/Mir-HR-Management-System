<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add name column
        Schema::table('employees', function (Blueprint $table) {
            $table->string('name', 200)->after('employee_code')->nullable();
        });

        // Migrate data
        DB::table('employees')->update([
            'name' => DB::raw("CONCAT(first_name, ' ', last_name)")
        ]);

        // Make name required if needed (it was nullable for the update to work correctly)
        // Schema::table('employees', function (Blueprint $table) {
        //     $table->string('name', 200)->nullable(false)->change();
        // });

        // Drop old columns
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('first_name', 100)->after('employee_code')->nullable();
            $table->string('last_name', 100)->after('first_name')->nullable();
        });

        // Migrate data back (splitting might be imperfect)
        DB::table('employees')->get()->each(function ($employee) {
            $parts = explode(' ', $employee->name, 2);
            DB::table('employees')->where('id', $employee->id)->update([
                'first_name' => $parts[0] ?? '',
                'last_name' => $parts[1] ?? '',
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
