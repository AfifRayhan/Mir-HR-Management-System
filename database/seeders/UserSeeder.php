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
                    $dept = Department::where('name', $deptData['name'])->first();
                    if ($dept) {
                        $dept->update(['incharge_id' => $managerEmp->id]);
                        
                        // Also update reporting manager for all employees in this department
                        // that don't have a reporting manager assigned yet
                        Employee::where('department_id', $dept->id)
                            ->where('id', '!=', $managerEmp->id)
                            ->whereNull('reporting_manager_id')
                            ->update(['reporting_manager_id' => $managerEmp->id]);
                    }
                }
            }
        }

        // Optional: Ensure an HR Admin always exists
        if ($hrAdminRole) {
            $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'hradmin@example.com');
            User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'HR Admin',
                    'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'secret')),
                    'employee_id' => null,
                    'role_id' => $hrAdminRole->id,
                    'status' => 'active',
                ]
            );
        }

        $inchargeIds = Department::whereNotNull('incharge_id')->pluck('incharge_id')->toArray();
        $employees = Employee::all();
        
        $csvPath = storage_path('app/private/password.csv');
        $passwordsByEmail = [];

        if (file_exists($csvPath)) {
            $this->command->info("Reading passwords from CSV...");
            if (($handle = fopen($csvPath, "r")) !== false) {
                $header = fgetcsv($handle, 1000, ","); // skip header
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if (count($data) >= 4) {
                        $email = strtolower(trim($data[3]));
                        $password = trim($data[2]);
                        if (!empty($email) && !empty($password)) {
                            $passwordsByEmail[$email] = $password;
                        }
                    }
                }
                fclose($handle);
            }
        }

        $defaultPassword = env('DEFAULT_USER_PASSWORD');
        if (empty($defaultPassword)) {
            // Generate a random 40-character password so no one can log in
            $defaultPassword = \Illuminate\Support\Str::random(40);
        }
        $hashedDefaultPassword = Hash::make($defaultPassword);
        
        $isProduction = app()->environment('production');

        $count = count($employees);
        $this->command->info("Creating/updating users for $count employees...");

        $hashCache = [];

        foreach ($employees as $index => $emp) {
            $roleId = in_array($emp->id, $inchargeIds) ? ($teamLeadRole->id ?? null) : ($employeeRole->id ?? null);
            
            $email = $emp->email;
            if (empty($email)) {
                $email = strtolower(str_replace(' ', '.', $emp->name)) . $emp->id . '@example.com';
            }

            $lookupEmail = strtolower(trim($email));
            
            if (!$isProduction && env('DEFAULT_USER_PASSWORD')) {
                // In local/development, force everyone to the default password for easy testing
                $userHashedPassword = $hashedDefaultPassword;
            } elseif (isset($passwordsByEmail[$lookupEmail])) {
                $plain = $passwordsByEmail[$lookupEmail];
                if (!isset($hashCache[$plain])) {
                    $hashCache[$plain] = Hash::make($plain);
                }
                $userHashedPassword = $hashCache[$plain];
            } else {
                $userHashedPassword = $hashedDefaultPassword;
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $emp->name,
                    'email' => $email,
                    'password' => $userHashedPassword,
                    'role_id' => $roleId,
                    'status' => 'active',
                    'employee_id' => $emp->employee_code,
                ]);
            } else {
                $user->update([
                    'role_id' => $roleId,
                    'password' => $userHashedPassword,
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
