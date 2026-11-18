<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DepartmentDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Management',            'code' => 'MGT'],
            ['name' => 'COMMERCIAL',            'code' => 'COM'],
            ['name' => 'ACCOUNTS AND FINANCE',  'code' => 'ACC'],
            ['name' => 'PROCUREMENT',           'code' => 'PRC'],
            ['name' => 'ADMIN',                 'code' => 'ADM'],
            ['name' => 'IT',                    'code' => 'IT'],
            ['name' => 'HR',                    'code' => 'HR'],
        ];

        $designations = [
            ['name' => 'CHIEF OPERATING OFFICER', 'code' => 'COO'],
            ['name' => 'MANAGER',                 'code' => 'MGR'],
            ['name' => 'ASSISTANCE MANAGER',      'code' => 'AM'],
            ['name' => 'DEPUTY MANAGER',          'code' => 'DM'],
            ['name' => 'SENIOR INTERNAL OFFICER', 'code' => 'SIO'],
            ['name' => 'ASSISTANT MANAGER',       'code' => 'ASM'],
            ['name' => 'EXECUTIVE',               'code' => 'EX'],
            ['name' => 'SENIOR EXECUTIVE',        'code' => 'SEX'],
            ['name' => 'ASSISTANT OFFICER',       'code' => 'AO'],
            ['name' => 'OFFICE ASSISTANCE',       'code' => 'OA'],
            ['name' => 'CLEANER',                 'code' => 'CLN'],
            ['name' => 'DRIVER',                  'code' => 'DRV'],
            ['name' => 'ADMIN',                   'code' => 'ADM'],
        ];

        foreach ($departments as $d) {
            Department::firstOrCreate(['name' => $d['name']], $d);
        }

        foreach ($designations as $d) {
            Designation::firstOrCreate(['name' => $d['name']], $d);
        }
    }
}
