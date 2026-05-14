<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['name' => 'Managing Director',      'short_name' => 'MD',   'priority' => 1],
            ['name' => 'Director',               'short_name' => 'DIR',  'priority' => 2],
            ['name' => 'ExecutiveDirector',      'short_name' => 'ED',   'priority' => 3],
            ['name' => 'CEO',                    'short_name' => 'CEO',  'priority' => 4],
            ['name' => 'COO',                    'short_name' => 'COO',  'priority' => 5],


            ['name' => 'Sr. GM',                 'short_name' => 'SGM',  'priority' => 6],
            ['name' => 'GM',                     'short_name' => 'GM',   'priority' => 7],
            ['name' => 'DGM',                    'short_name' => 'DGM',  'priority' => 8],
            ['name' => 'AGM',                   'short_name' => 'AGM',  'priority' => 9],
            ['name' => 'Sr. Manager',            'short_name' => 'SMG',  'priority' => 10],
            ['name' => 'Manager',                'short_name' => 'MGR',  'priority' => 11],
            ['name' => 'Deputy Manager',         'short_name' => 'DM',   'priority' => 12],
            ['name' => 'Assistant Manager',      'short_name' => 'AM',   'priority' => 13],


            ['name' => 'Sr. System Engineer',    'short_name' => 'SSY',  'priority' => 14],
            ['name' => 'Sr. Software Engineer',  'short_name' => 'SSE',  'priority' => 14],
            ['name' => 'System Engineer',        'short_name' => 'SYE',  'priority' => 15],
            ['name' => 'Assistant Engineer',     'short_name' => 'AE',   'priority' => 16],


            ['name' => 'Sr. Technical Officer',  'short_name' => 'STO',  'priority' => 14],
            ['name' => 'Technical Officer',      'short_name' => 'TO',   'priority' => 17],
            ['name' => 'Jr. Technical Officer',  'short_name' => 'JTO',  'priority' => 18],


            ['name' => 'Sr. Technician',         'short_name' => 'STC',  'priority' => 19],
            ['name' => 'Technician',             'short_name' => 'TEC',  'priority' => 20],
            ['name' => 'Electrician',            'short_name' => 'ELE',  'priority' => 21],
            ['name' => 'Line Man',               'short_name' => 'LM',   'priority' => 22],

            ['name' => 'Sr. Executive',          'short_name' => 'SEX',  'priority' => 15],
            ['name' => 'Executive',              'short_name' => 'EXC',  'priority' => 16],
            ['name' => 'Jr. Executive',          'short_name' => 'JEX',  'priority' => 18],
            ['name' => 'Trainee Executive',      'short_name' => 'TEX',  'priority' => 19],
            


            ['name' => 'Head of Operations',     'short_name' => 'HOP',  'priority' => 21],
            ['name' => 'Restaurant Manager',     'short_name' => 'RM',   'priority' => 22],
            ['name' => 'Shift Manager',          'short_name' => 'SM',   'priority' => 23],
            ['name' => 'Supervisor',             'short_name' => 'SUP',  'priority' => 24],
            ['name' => 'Storekeeper',            'short_name' => 'SKP',  'priority' => 25],
            

            ['name' => 'Asst. Pastry Chef',      'short_name' => 'APC',  'priority' => 25],
            ['name' => 'DCDP',                   'short_name' => 'DCDP', 'priority' => 26],
            ['name' => 'Team Leader, Barista',   'short_name' => 'TLB',  'priority' => 27],
            
            ['name' => 'Café Assistant',          'short_name' => 'CA',   'priority' => 29],
            ['name' => 'SOUS CHEF',              'short_name' => 'SC',   'priority' => 30],
            ['name' => 'Sr. Waiter',             'short_name' => 'SWT',  'priority' => 31],
            ['name' => 'Waiter',                 'short_name' => 'WTR',  'priority' => 32],
            ['name' => 'Waitress',               'short_name' => 'WTS',  'priority' => 32],
            ['name' => 'Commis 2',               'short_name' => 'C2',   'priority' => 33],
            ['name' => 'Commis 3',               'short_name' => 'C3',   'priority' => 34],
            ['name' => 'Barista',                'short_name' => 'BAR',  'priority' => 30],
            ['name' => 'Host',                   'short_name' => 'HST',  'priority' => 35],
            ['name' => 'Security Supervisor',    'short_name' => 'SS',   'priority' => 36],
            ['name' => 'Housekeeper',            'short_name' => 'HK',   'priority' => 37],
            
            
            ['name' => 'Collection Assistant',   'short_name' => 'COA',  'priority' => 41],
            ['name' => 'Office Assistant',       'short_name' => 'OA',   'priority' => 42],
            ['name' => 'Cook',                   'short_name' => 'CK',   'priority' => 43],
            ['name' => 'Driver',                 'short_name' => 'DRV',  'priority' => 43],
            ['name' => 'Peon',                   'short_name' => 'PEO',  'priority' => 44],
            ['name' => 'Cleaner',                'short_name' => 'CLN',  'priority' => 45],
        ];

        foreach ($designations as $desig) {
            Designation::updateOrCreate(['name' => $desig['name']], $desig);
        }
    }
}
