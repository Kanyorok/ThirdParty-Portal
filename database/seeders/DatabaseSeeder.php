<?php

namespace Database\Seeders;

use App\Enums\Core\ModulesEnum;
use App\Models\Budget\BudgetActivityMaster;
use App\Models\Budget\BudgetGLAccountSubType;
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
        $this->call(BudgetDriverTypes::class);
        $this->call(BudgetGLAccountsSeeder::class);
        $this->call(BudgetGLAccountSubTypeSeeder::class);
        $this->call(BudgetProductTypeSeeder::class);
        $this->call(BudgetLineCategorySeeder::class);
        $this->call(BudgetLineSeeder::class);
        $this->call(BudgetLinesGLAccountsSeeder::class);
        $this->call(BudgetProductsSeeder::class);
        $this->call(BudgetDriversMasterSeeder::class);
        $this->call(BudgetActivityMasterSeeder::class);
        $this->call(BudgetDriverRatesSeeder::class);
        $this->call(BudgetMonthlyAllocationsSeeder::class);
        $this->call(BudgetActivitiesSeeder::class);
        $this->call(BudgetDriverProjectionsSeeder::class);
        $this->call(BudgetDriverProjectionsDataSeeder::class);
        $this->call(BudgetSeeder::class);
    }
}
