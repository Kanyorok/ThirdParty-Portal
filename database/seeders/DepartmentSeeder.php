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
        Department::create([
            'DepartmentID' => 'D001',
            'Name' => 'Marketing',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);
    }
}
