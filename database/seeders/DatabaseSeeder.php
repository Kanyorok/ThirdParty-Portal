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
        $this->call(SysFilterSeeder::class);
        $this->call(DocumentValidationTypeSeeder::class);
        $this->call(CurrencySeeder::class);
        //$this->call(LocalitySeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(CommitteeSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(JobGradeSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(ItemCategoriesSeeder::class);
        $this->call(UnitOfMeasureSeeder::class);
        $this->call(ItemMasterListSeeder::class);
        // $this->call(DepartmentNeedsSeeder::class);
        // $this->call(SupplierSeeder::class);
        $this->call(BudgetMasterSeeder::class);
        $this->call(BudgetPeriodTypeSeeder::class);
        //        $this->call(BudgetPeriodSeeder::class);
        //        $this->call(BudgetPlanningMethodSeeder::class);
        //        $this->call(BudgetScenarioPlanningSeeder::class);
        $this->call(BudgetRatesSeeder::class);
        //        $this->call(BudgetDriverTypes::class);
        //        $this->call(BudgetGLAccountsSeeder::class);
        //        $this->call(BudgetGLAccountSubTypeSeeder::class);
        $this->call(BudgetProductTypeSeeder::class);
        $this->call(BudgetLineCategorySeeder::class);
        //        $this->call(BudgetLineSeeder::class);
        //$this->call(BudgetLinesGLAccountsSeeder::class);
        //        $this->call(BudgetProductsSeeder::class);
        //        $this->call(BudgetDriversMasterSeeder::class);
        //$this->call(BudgetActivityMasterSeeder::class);
        //$this->call(BudgetDriverRatesSeeder::class);
        //        $this->call(BudgetMonthlyAllocationsSeeder::class);
        $this->call(BudgetSeeder::class);
        //        $this->call(BudgetActivitiesSeeder::class);
        $this->call(BudgetDriverProjectionsSeeder::class);
        //        $this->call(BudgetDriverProjectionsDataSeeder::class);
        $this->call(CategoryMasterSeeder::class);
        $this->call(PropertyTypeSeeder::class);
        // $this->call(PropertyRegistrySeeder::class);
        $this->call(PropertyBlockSeeder::class);
        $this->call(PropertyFloorSeeder::class);
        $this->call(PropertyUnitSeeder::class);
 
        //$this->call(TenantRegistrySeeder::class);
        $this->call(FinanceGLTypeGroupSeeder::class);
        $this->call(FinanceGLSubAccountTypeSeeder::class);
        $this->call(FinanceGLAccountsSeeder::class);
        $this->call(FinanceSegmentOrderSeeder::class);
//        $this->call(OrderLinesSeeder::class);
//         $this->call(OrderSeeder::class);
//         $this->call(GoodsReceiptsSeeder::class);
 
        $this->call(FinanceTransactionTypesSeeder::class);
        $this->call(FinanceModuleTransactionSeeder::class);
        $this->call(FinanceGlTransactionsMappingSeeder::class);
        // Ensure GRN transaction types and GL mappings (incl. GRN-SERVICE) are present
        $this->call(GRNTransactionTypesSeeder::class);
 
        $this->call(LegalClauseSeeder::class);
        $this->call(LegalCasesSeeder::class);
        $this->call(LegalCaseEvidenceSeeder::class);
        $this->call(LegalObligationsSeeder::class);
        $this->call(LegalSearchRequestsSeeder::class);
        $this->call(LegalIntellectualPropertiesSeeder::class);
        $this->call(LegalLoanSecuritiesSeeder::class);
        $this->call(LegalCaseCounselSeeder::class);
        $this->call(LegalCaseOutcomeSeeder::class);
        $this->call(RegulatoryObligationsSeeder::class);
        $this->call(ComplianceMastersSeeder::class);
        $this->call(ComplianceObligationsSeeder::class);
        $this->call(ComplianceControlsSeeder::class);
        $this->call(ComplianceIncidentsSeeder::class);
        $this->call(ComplianceFilingsSeeder::class);
 
        $this->call(SystemBankSettingSeeder::class);
        $this->call(BanksSeeder::class);
        $this->call(BankBranchesSeeder::class);
 
 
//         $this->call(RegulatoryObligationsSeeder::class);
//         $this->call(ComplianceMastersSeeder::class);
//         $this->call(ComplianceObligationsSeeder::class);
//         $this->call(ComplianceControlsSeeder::class);
//         $this->call(ComplianceIncidentsSeeder::class);
//         $this->call(ComplianceFilingsSeeder::class);
 
        $this->call(financeroleseeder::class);
        $this->call(ThirdPartyTypesSeeder::class);
        $this->call(MedicalFundsCatalogSeeder::class);
        // $this->call(GRNPOSeeder::class);
        $this->call(KpiSeeder::class);
        $this->call(StatutorySeeder::class);
        $this->call(LeaveTypeSeeder::class);
        $this->call(ExitSeeder::class);
        $this->call(HRSharedDocumentsSeeder::class);
        $this->call(HRTrainingSeeder::class);
        $this->call(HROvertimeSeeder::class);
    }
}
