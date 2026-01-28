<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


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
        $this->call(LocalitySeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(ItemCategoriesSeeder::class);
        $this->call(UnitOfMeasureSeeder::class);
        $this->call(BudgetMasterSeeder::class);
        $this->call(BudgetPeriodTypeSeeder::class);
        $this->call(BudgetRatesSeeder::class);
        $this->call(BudgetProductTypeSeeder::class);
        $this->call(BudgetLineCategorySeeder::class);
        $this->call(BudgetSeeder::class);
        $this->call(BudgetDriverProjectionsSeeder::class);
        $this->call(CategoryMasterSeeder::class);
        $this->call(PropertyTypeSeeder::class);
        $this->call(PropertyBlockSeeder::class);
        $this->call(PropertyFloorSeeder::class);
        $this->call(PropertyUnitSeeder::class);

        $this->call(FinanceGLTypeGroupSeeder::class);
        $this->call(FinanceGLSubAccountTypeSeeder::class);
        $this->call(FinanceGLAccountsSeeder::class);
        $this->call(FinanceSegmentOrderSeeder::class);

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



        $this->call(financeroleseeder::class);
        $this->call(ThirdPartyTypesSeeder::class);
        $this->call(MedicalFundsCatalogSeeder::class);
        $this->call(WorkFlowTypesSeeder::class);
    }
}
