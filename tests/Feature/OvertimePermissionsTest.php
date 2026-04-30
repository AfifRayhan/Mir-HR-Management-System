<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Overtime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimePermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $subordinate;
    protected $otherEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'HR Admin']);
        $managerRole = Role::create(['name' => 'Team Lead']);
        $employeeRole = Role::create(['name' => 'Employee']);

        $designation = Designation::create(['name' => 'Developer', 'is_ot_eligible' => true]);
        $dept = Department::create(['name' => 'IT']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $adminEmp = Employee::factory()->create([
            'user_id' => $this->admin->id,
            'designation_id' => $designation->id,
            'department_id' => $dept->id
        ]);

        $this->manager = User::factory()->create(['role_id' => $managerRole->id]);
        $managerEmp = Employee::factory()->create([
            'user_id' => $this->manager->id,
            'designation_id' => $designation->id,
            'department_id' => $dept->id
        ]);

        $this->subordinate = User::factory()->create(['role_id' => $employeeRole->id]);
        $subEmp = Employee::factory()->create([
            'user_id' => $this->subordinate->id,
            'designation_id' => $designation->id,
            'department_id' => $dept->id,
            'reporting_manager_id' => $managerEmp->id
        ]);

        $this->otherEmployee = User::factory()->create(['role_id' => $employeeRole->id]);
        Employee::factory()->create([
            'user_id' => $this->otherEmployee->id,
            'designation_id' => $designation->id,
            'department_id' => $dept->id
        ]);
    }

    public function test_employee_can_edit_own_overtime()
    {
        $employee = Employee::where('user_id', $this->subordinate->id)->first();
        
        $response = $this->actingAs($this->subordinate)
            ->get(route('overtimes.index', ['employee_id' => $employee->id]));
        
        $response->assertOk();
        $response->assertSee('0.00'); // total hours display
        $response->assertSee(__('Save Overtime Records'));
        $response->assertDontSee('readonly');

        // Try to save
        $response = $this->actingAs($this->subordinate)
            ->post(route('overtimes.save'), [
                'employee_id' => $employee->id,
                'month' => '04',
                'year' => '2026',
                'ot' => [
                    '2026-04-01' => ['start' => '17:00', 'stop' => '19:00']
                ]
            ]);
        
        $response->assertRedirect();
    }

    public function test_manager_can_edit_subordinate_overtime()
    {
        $subEmp = Employee::where('user_id', $this->subordinate->id)->first();
        
        $response = $this->actingAs($this->manager)
            ->get(route('overtimes.index', ['employee_id' => $subEmp->id]));
        
        $response->assertOk();
        $response->assertSee('Save Overtime Records');
        $response->assertDontSee('readonly');

        // Try to save
        $response = $this->actingAs($this->manager)
            ->post(route('overtimes.save'), [
                'employee_id' => $subEmp->id,
                'month' => '04',
                'year' => '2026',
                'ot' => [
                    '2026-04-01' => ['start' => '17:00', 'stop' => '19:00']
                ]
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('overtimes', ['employee_id' => $subEmp->id, 'total_ot_hours' => 2]);
    }

    public function test_manager_can_edit_own_overtime()
    {
        $managerEmp = Employee::where('user_id', $this->manager->id)->first();
        
        $response = $this->actingAs($this->manager)
            ->get(route('overtimes.index', ['employee_id' => $managerEmp->id]));
        
        $response->assertOk();
        $response->assertSee(__('Save Overtime Records'));

        $response = $this->actingAs($this->manager)
            ->post(route('overtimes.save'), [
                'employee_id' => $managerEmp->id,
                'month' => '04',
                'year' => '2026',
                'ot' => [
                    '2026-04-01' => ['start' => '17:00', 'stop' => '19:00']
                ]
            ]);
        
        $response->assertRedirect();
    }

    public function test_admin_can_edit_own_overtime()
    {
        $adminEmp = Employee::where('user_id', $this->admin->id)->first();
        
        $response = $this->actingAs($this->admin)
            ->get(route('overtimes.index', ['employee_id' => $adminEmp->id]));
        
        $response->assertOk();
        $response->assertSee('Save Overtime Records');

        $response = $this->actingAs($this->admin)
            ->post(route('overtimes.save'), [
                'employee_id' => $adminEmp->id,
                'month' => '04',
                'year' => '2026',
                'ot' => [
                    '2026-04-01' => ['start' => '17:00', 'stop' => '19:00']
                ]
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('overtimes', ['employee_id' => $adminEmp->id, 'total_ot_hours' => 2]);
    }

    public function test_unauthorized_employee_cannot_view_other_overtime()
    {
        $otherEmp = Employee::where('user_id', $this->otherEmployee->id)->first();
        
        $response = $this->actingAs($this->subordinate)
            ->get(route('overtimes.index', ['employee_id' => $otherEmp->id]));
        
        $response->assertStatus(403);
    }
}
