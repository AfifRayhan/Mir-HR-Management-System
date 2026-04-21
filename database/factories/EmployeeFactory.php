<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'employee_code' => $this->faker->unique()->numerify('#####'),
            'hrm_employee_id' => $this->faker->unique()->numerify('HRM-#####'),
            'joining_date' => now()->subYear(),
            'status' => 'active',
            'employee_type' => 'Regular',
        ];
    }
}
