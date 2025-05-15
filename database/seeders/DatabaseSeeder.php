<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(TeamSeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(CodeDetailSeeder::class);
        $this->call(LocalitySeeder::class);
        $this->call(SysFilterSeeder::class);
        $this->call(SysFilterSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(CommitteeSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(EmployeeSeeder::class);
    }
}
