<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use App\Models\HRM\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    $actor = SystemHelper::user();

    $departments = [
        ['DepartmentID' => 'D001', 'Name' => 'Marketing'],
        ['DepartmentID' => 'D002', 'Name' => 'Finance'],
        ['DepartmentID' => 'D003', 'Name' => 'Procurement'],
        ['DepartmentID' => 'D004', 'Name' => 'Human Resource'],
        ['DepartmentID' => 'D005', 'Name' => 'Research and Development'],
        ['DepartmentID' => 'D006', 'Name' => 'ICT Support'],
    ];

    foreach ($departments as $dept) {
        Department::create([
            'DepartmentID' => $dept['DepartmentID'],
            'Name' => $dept['Name'],
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);
    }
}
}
