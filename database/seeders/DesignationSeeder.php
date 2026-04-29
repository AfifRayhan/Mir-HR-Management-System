<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['name' => 'AGM',                   'short_name' => 'AGM',  'priority' => 3],
            ['name' => 'Assistant Engineer',     'short_name' => 'AE',   'priority' => 5],
            ['name' => 'Assistant Manager',      'short_name' => 'AM',   'priority' => 4],
            ['name' => 'Asst. Pastry Chef',      'short_name' => 'APC',  'priority' => 7],
            ['name' => 'Barista',                'short_name' => 'BAR',  'priority' => 8],
            ['name' => 'CEO',                    'short_name' => 'CEO',  'priority' => 1],
            ['name' => 'COO',                    'short_name' => 'COO',  'priority' => 1],
            ['name' => 'Café Assistant',          'short_name' => 'CA',   'priority' => 8],
            ['name' => 'Cleaner',                'short_name' => 'CLN',  'priority' => 10],
            ['name' => 'Collection Assistant',   'short_name' => 'COA',  'priority' => 7],
            ['name' => 'Commis 2',               'short_name' => 'C2',   'priority' => 7],
            ['name' => 'Commis 3',               'short_name' => 'C3',   'priority' => 8],
            ['name' => 'Cook',                   'short_name' => 'CK',   'priority' => 7],
            ['name' => 'DCDP',                   'short_name' => 'DCDP', 'priority' => 6],
            ['name' => 'DGM',                    'short_name' => 'DGM',  'priority' => 3],
            ['name' => 'Deputy Manager',         'short_name' => 'DM',   'priority' => 4],
            ['name' => 'Director',               'short_name' => 'DIR',  'priority' => 2],
            ['name' => 'Driver',                 'short_name' => 'DRV',  'priority' => 9],
            ['name' => 'Electrician',            'short_name' => 'ELE',  'priority' => 8],
            ['name' => 'Executive',              'short_name' => 'EXC',  'priority' => 6],
            ['name' => 'ExecutiveDirector',      'short_name' => 'ED',   'priority' => 2],
            ['name' => 'GM',                     'short_name' => 'GM',   'priority' => 2],
            ['name' => 'Head of Operations',     'short_name' => 'HOP',  'priority' => 3],
            ['name' => 'Host',                   'short_name' => 'HST',  'priority' => 8],
            ['name' => 'Housekeeper',            'short_name' => 'HK',   'priority' => 9],
            ['name' => 'Jr. Executive',          'short_name' => 'JEX',  'priority' => 7],
            ['name' => 'Jr. Technical Officer',  'short_name' => 'JTO',  'priority' => 7],
            ['name' => 'Line Man',               'short_name' => 'LM',   'priority' => 8],
            ['name' => 'Manager',                'short_name' => 'MGR',  'priority' => 4],
            ['name' => 'Managing Director',      'short_name' => 'MD',   'priority' => 1],
            ['name' => 'Office Assistant',       'short_name' => 'OA',   'priority' => 8],
            ['name' => 'Peon',                   'short_name' => 'PEO',  'priority' => 10],
            ['name' => 'Restaurant Manager',     'short_name' => 'RM',   'priority' => 4],
            ['name' => 'SOUS CHEF',              'short_name' => 'SC',   'priority' => 8],
            ['name' => 'Security Supervisor',    'short_name' => 'SS',   'priority' => 6],
            ['name' => 'Shift Manager',          'short_name' => 'SM',   'priority' => 5],
            ['name' => 'Sr. Executive',          'short_name' => 'SEX',  'priority' => 5],
            ['name' => 'Sr. GM',                 'short_name' => 'SGM',  'priority' => 2],
            ['name' => 'Sr. Manager',            'short_name' => 'SMG',  'priority' => 3],
            ['name' => 'Sr. Software Engineer',  'short_name' => 'SSE',  'priority' => 5],
            ['name' => 'Sr. System Engineer',    'short_name' => 'SSY',  'priority' => 5],
            ['name' => 'Sr. Technical Officer',  'short_name' => 'STO',  'priority' => 5],
            ['name' => 'Sr. Technician',         'short_name' => 'STC',  'priority' => 6],
            ['name' => 'Sr. Waiter',             'short_name' => 'SWT',  'priority' => 7],
            ['name' => 'Storekeeper',            'short_name' => 'SKP',  'priority' => 7],
            ['name' => 'Supervisor',             'short_name' => 'SUP',  'priority' => 6],
            ['name' => 'System Engineer',        'short_name' => 'SYE',  'priority' => 5],
            ['name' => 'Team Leader, Barista',   'short_name' => 'TLB',  'priority' => 6],
            ['name' => 'Technical Officer',      'short_name' => 'TO',   'priority' => 6],
            ['name' => 'Technician',             'short_name' => 'TEC',  'priority' => 7],
            ['name' => 'Trainee Executive',      'short_name' => 'TEX',  'priority' => 8],
            ['name' => 'Waiter',                 'short_name' => 'WTR',  'priority' => 8],
            ['name' => 'Waitress',               'short_name' => 'WTS',  'priority' => 8],
        ];

        foreach ($designations as $desig) {
            Designation::updateOrCreate(['name' => $desig['name']], $desig);
        }
    }
}
