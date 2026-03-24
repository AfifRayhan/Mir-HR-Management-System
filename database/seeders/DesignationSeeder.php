<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['name' => 'AGM', 'short_name' => 'AGM', 'priority' => 8],
            ['name' => 'Assistant Engineer', 'short_name' => 'AE', 'priority' => 5],
            ['name' => 'Assistant Manager', 'short_name' => 'AM', 'priority' => 6],
            ['name' => 'Asst. Pastry Chef', 'short_name' => 'APC', 'priority' => 5],
            ['name' => 'Barista', 'short_name' => 'BAR', 'priority' => 4],
            ['name' => 'CEO', 'short_name' => 'CEO', 'priority' => 10],
            ['name' => 'COO', 'short_name' => 'COO', 'priority' => 10],
            ['name' => 'Café Assistant', 'short_name' => 'CA', 'priority' => 3],
            ['name' => 'Cleaner', 'short_name' => 'CLN', 'priority' => 1],
            ['name' => 'Collection Assistant', 'short_name' => 'COA', 'priority' => 4],
            ['name' => 'Commis 2', 'short_name' => 'C2', 'priority' => 4],
            ['name' => 'Commis 3', 'short_name' => 'C3', 'priority' => 3],
            ['name' => 'Cook', 'short_name' => 'CK', 'priority' => 4],
            ['name' => 'DCDP', 'short_name' => 'DCDP', 'priority' => 5],
            ['name' => 'DGM', 'short_name' => 'DGM', 'priority' => 8],
            ['name' => 'Deputy Manager', 'short_name' => 'DM', 'priority' => 7],
            ['name' => 'Director', 'short_name' => 'DIR', 'priority' => 9],
            ['name' => 'Driver', 'short_name' => 'DRV', 'priority' => 2],
            ['name' => 'Electrician', 'short_name' => 'ELE', 'priority' => 3],
            ['name' => 'Executive', 'short_name' => 'EXC', 'priority' => 5],
            ['name' => 'ExecutiveDirector', 'short_name' => 'ED', 'priority' => 9],
            ['name' => 'GM', 'short_name' => 'GM', 'priority' => 9],
            ['name' => 'Head of Operations', 'short_name' => 'HOP', 'priority' => 8],
            ['name' => 'Host', 'short_name' => 'HST', 'priority' => 3],
            ['name' => 'Housekeeper', 'short_name' => 'HK', 'priority' => 2],
            ['name' => 'Jr. Executive', 'short_name' => 'JEX', 'priority' => 4],
            ['name' => 'Jr. Technical Officer', 'short_name' => 'JTO', 'priority' => 4],
            ['name' => 'Line Man', 'short_name' => 'LM', 'priority' => 3],
            ['name' => 'Manager', 'short_name' => 'MGR', 'priority' => 7],
            ['name' => 'Managing Director', 'short_name' => 'MD', 'priority' => 10],
            ['name' => 'Office Assistant', 'short_name' => 'OA', 'priority' => 3],
            ['name' => 'Peon', 'short_name' => 'PEO', 'priority' => 1],
            ['name' => 'Restaurant Manager', 'short_name' => 'RM', 'priority' => 7],
            ['name' => 'SOUS CHEF', 'short_name' => 'SC', 'priority' => 6],
            ['name' => 'Security Supervisor', 'short_name' => 'SS', 'priority' => 5],
            ['name' => 'Shift Manager', 'short_name' => 'SM', 'priority' => 6],
            ['name' => 'Sr. Executive', 'short_name' => 'SEX', 'priority' => 6],
            ['name' => 'Sr. GM', 'short_name' => 'SGM', 'priority' => 9],
            ['name' => 'Sr. Manager', 'short_name' => 'SMG', 'priority' => 8],
            ['name' => 'Sr. Software Engineer', 'short_name' => 'SSE', 'priority' => 7],
            ['name' => 'Sr. System Engineer', 'short_name' => 'SSY', 'priority' => 7],
            ['name' => 'Sr. Technical Officer', 'short_name' => 'STO', 'priority' => 6],
            ['name' => 'Sr. Technician', 'short_name' => 'STC', 'priority' => 5],
            ['name' => 'Sr. Waiter', 'short_name' => 'SWT', 'priority' => 4],
            ['name' => 'Storekeeper', 'short_name' => 'SKP', 'priority' => 4],
            ['name' => 'Supervisor', 'short_name' => 'SUP', 'priority' => 6],
            ['name' => 'System Engineer', 'short_name' => 'SYE', 'priority' => 6],
            ['name' => 'Team Leader, Barista', 'short_name' => 'TLB', 'priority' => 5],
            ['name' => 'Technical Officer', 'short_name' => 'TO', 'priority' => 5],
            ['name' => 'Technician', 'short_name' => 'TEC', 'priority' => 4],
            ['name' => 'Trainee Executive', 'short_name' => 'TEX', 'priority' => 3],
            ['name' => 'Waiter', 'short_name' => 'WTR', 'priority' => 3],
            ['name' => 'Waitress', 'short_name' => 'WTS', 'priority' => 3],
        ];

        foreach ($designations as $desig) {
            Designation::firstOrCreate(['name' => $desig['name']], $desig);
        }
    }
}
