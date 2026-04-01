<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed example users for each core role.
     */
    public function run(): void
    {
        // 1. Ensure essential roles exist
        $employeeRole = Role::firstOrCreate(['name' => 'Employee'], ['description' => 'Regular Employee']);
        $teamLeadRole = Role::firstOrCreate(['name' => 'Team Lead'], ['description' => 'Department Head / Team Lead']);
        $hrAdminRole = Role::firstOrCreate(['name' => 'HR Admin'], ['description' => 'HR Administrator']);

        // 2. Late-binding Department Incharge IDs
        // We do this here because EmployeeSeeder has already run, so employees now exist
        foreach (DepartmentSeeder::getDepartments() as $deptData) {
            if ($deptData['incharge_code']) {
                $managerEmp = Employee::where('employee_code', $deptData['incharge_code'])->first();
                if ($managerEmp) {
                    Department::where('name', $deptData['name'])->update([
                        'incharge_id' => $managerEmp->id
                    ]);
                }
            }
        }

        // Optional: Ensure an HR Admin always exists
        if ($hrAdminRole) {
            User::firstOrCreate(
                ['email' => 'hradmin@example.com'],
                [
                    'name' => 'HR Admin',
                    'password' => Hash::make('password'),
                    'employee_id' => null,
                    'role_id' => $hrAdminRole->id,
                    'status' => 'active',
                ]
            );
        }

        $inchargeIds = Department::whereNotNull('incharge_id')->pluck('incharge_id')->toArray();
        $employees = Employee::all();
        $hashedPassword = Hash::make('password');
        $count = count($employees);
        $this->command->info("Creating/updating users for $count employees...");

        foreach ($employees as $index => $emp) {
            $roleId = in_array($emp->id, $inchargeIds) ? ($teamLeadRole->id ?? null) : ($employeeRole->id ?? null);
            
            $email = $emp->email;
            if (empty($email)) {
                $email = strtolower(str_replace(' ', '.', $emp->name)) . $emp->id . '@example.com';
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $emp->name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role_id' => $roleId,
                    'status' => 'active',
                    'employee_id' => $emp->employee_code,
                ]);
            } else {
                $user->update([
                    'role_id' => $roleId,
                    'password' => $hashedPassword,
                ]);
            }

            $emp->update(['user_id' => $user->id]);

            if (($index + 1) % 50 === 0) {
                $this->command->info("Processed " . ($index + 1) . " employees...");
            }
        }
        $this->command->info("UserSeeder completed.");
    }
}
