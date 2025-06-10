<?php

namespace Database\Seeders;

use App\Enums\Core\ModulesEnum;
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
        $this->call(CurrencySeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(CommitteeSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(ItemCategoriesSeeder::class);
        $this->call(ItemTypeSeeder::class);
        $this->call(InventoryTypeSeeder::class);
        $this->call(UnitOfMeasureSeeder::class);
        $this->call(ItemMasterListSeeder::class);
        $this->call(DepartmentNeedsSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(BudgetMasterSeeder::class);
        $this->call(BudgetPeriodTypeSeeder::class);
        $this->call(BudgetPeriodSeeder::class);
        $this->call(BudgetPlanningMethodSeeder::class);
        $this->call(BudgetScenarioPlanningSeeder::class);
        $this->call(BudgetRatesSeeder::class);
    }
}
