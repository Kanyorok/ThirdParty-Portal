<?php

use App\Http\Controllers\Budget\BranchPerformanceController;
use App\Http\Controllers\Budget\BudgetActivitiesController;
use App\Http\Controllers\Budget\BudgetActivitiesMasterController;
use App\Http\Controllers\Budget\BudgetApprovalController;
use App\Http\Controllers\Budget\BudgetConsolidationController;
use App\Http\Controllers\Budget\BudgetDriversController;
use App\Http\Controllers\Budget\BudgetDriversSetupController;
use App\Http\Controllers\Budget\BudgetFormulaController;
use App\Http\Controllers\Budget\BudgetGLLineEntryController;
use App\Http\Controllers\Budget\BudgetGLMappingController;
use App\Http\Controllers\Budget\BudgetKPIscorecardsController;
use App\Http\Controllers\Budget\BudgetKPIscorecardsOfficerController;
use App\Http\Controllers\Budget\BudgetLineCategoriesController;
use App\Http\Controllers\Budget\BudgetLineMappingController;
use App\Http\Controllers\Budget\BudgetLinesController;
use App\Http\Controllers\Budget\BudgetMonthlyProjectionController;
use App\Http\Controllers\Budget\BudgetPeriodController;
use App\Http\Controllers\Budget\BudgetPeriodTypesController;
use App\Http\Controllers\Budget\BudgetPlanningMethodsController;
use App\Http\Controllers\Budget\BudgetProductMasterController;
use App\Http\Controllers\Budget\BudgetProductTypeController;
use App\Http\Controllers\Budget\BudgetProjectionsController;
use App\Http\Controllers\Budget\BudgetRatesController;
use App\Http\Controllers\Budget\BudgetSceneriosController;
use App\Http\Controllers\Budget\BudgetSubmitController;
use App\Http\Controllers\Budget\BudgetTopDownAllocationController;
use App\Http\Controllers\Budget\BudgetVarianceAnalysisController;
use App\Http\Controllers\Budget\BudgetvsActualbyBranchController;
use App\Http\Controllers\Budget\BudgetvsActualDashboardController;
use App\Http\Controllers\Budget\BusinessAnalyticsDashboardController;
use App\Http\Controllers\Budget\CapitalAdequacyController;
use App\Http\Controllers\Budget\CBKRegulatoryRatiosController;
use App\Http\Controllers\Budget\CBSSyncController;
use App\Http\Controllers\Budget\CostToIncomeController;
use App\Http\Controllers\Budget\DataExportToolsProductController;
use App\Http\Controllers\Budget\DataSyncLogsController;
use App\Http\Controllers\Budget\DepositBookTrendsController;
use App\Http\Controllers\Budget\DepositGrowthByOfficerController;
use App\Http\Controllers\Budget\DormantCASAController;
use App\Http\Controllers\Budget\ECLProvisioningController;
use App\Http\Controllers\Budget\ExpenseByGLStatisticsController;
use App\Http\Controllers\Budget\IncomeByBranchStatisticsController;
use App\Http\Controllers\Budget\KPIDashboardsController;
use App\Http\Controllers\Budget\LiquidityController;
use App\Http\Controllers\Budget\LoanBookTrendsController;
use App\Http\Controllers\Budget\LoanToDepositController;
use App\Http\Controllers\Budget\LoanYieldbyProductController;
use App\Http\Controllers\Budget\MultidimensionalStatisticsController;
use App\Http\Controllers\Budget\NPLRiskController;
use App\Http\Controllers\Budget\NPLTrendByProductController;
use App\Http\Controllers\Budget\OfficerPerformanceController;
use App\Http\Controllers\Budget\ProductProfitabilityController;
use App\Http\Controllers\Budget\RegulatoryRatiosController;
use App\Http\Controllers\Budget\ReportsController;
use App\Http\Controllers\Budget\ReturnOnAssetsController;
use App\Http\Controllers\Budget\ReturnOnEquityController;
use App\Http\Controllers\Budget\TopContibutorsController;
use App\Http\Controllers\Budget\TopCurrentAccountsController;
use App\Http\Controllers\Budget\TopDepositorsController;
use App\Http\Controllers\Budget\TopLoansController;
use App\Http\Controllers\Budget\TopSavingAccountsController;
use App\Http\Controllers\Budget\TrendAndGrowthController;
use App\Http\Controllers\Budget\YieldRateController;
use Illuminate\Support\Facades\Route;


Route::namespace('Budget')->prefix('budget')->group(function () {
    Route::resource('budgetline', BudgetLinesController::class);
    Route::resource('budgetperiod', BudgetPeriodController::class);
    Route::resource('budgetproductmaster', BudgetProductMasterController::class);
    Route::resource('budgetproducttype', BudgetProductTypeController::class);
    Route::resource('budgetlinemapping', BudgetLineMappingController::class);
    Route::resource('budgetglmapping', BudgetGLMappingController::class);
    Route::resource('budgetdrivers', BudgetDriversController::class);
    Route::resource('budgetlinecategories', BudgetLineCategoriesController::class);
    Route::resource('budgetactivities', BudgetActivitiesController::class);
    Route::resource('yieldexpenserate', YieldRateController::class);
    //Route::resource('budgetprojections', BudgetProjectionsController::class);
    Route::resource('budgetprojections', BudgetProjectionsController::class);
    Route::resource('entrybyglline', BudgetGLLineEntryController::class);
    Route::get('/entrybyglline/glview/{budgetId}', [BudgetGLLineEntryController::class, 'glview'])->name('entrybyglline.glview');
    Route::resource('submitapproval', BudgetSubmitController::class);
    Route::resource('budgetapproval', BudgetApprovalController::class);
    Route::resource('topdownallocation', BudgetTopDownAllocationController::class);
    Route::post('/topdownallocation/display', [BudgetTopDownAllocationController::class, 'display'])->name('topdownallocation.display');
    Route::resource('topdownallocation', BudgetTopDownAllocationController::class);
    Route::resource('activitymaster', BudgetActivitiesMasterController::class);
    Route::resource('budgetscenerios', BudgetSceneriosController::class);
    Route::resource('budgetformula', BudgetFormulaController::class);
    Route::resource('budgetconsolidation', BudgetConsolidationController::class);
    Route::resource('budgetvsactualdashboard', BudgetvsActualDashboardController::class);
    Route::resource('budgetvarianceanalysis', BudgetVarianceAnalysisController::class);
    Route::resource('kpiscorecards', BudgetKPIscorecardsController::class);
    Route::resource('kpiscorecardsofficer', BudgetKPIscorecardsOfficerController::class);
    Route::resource('monthly', BudgetMonthlyProjectionController::class);
    Route::resource('topcontributors', TopContibutorsController::class);
    Route::resource('regulatoryratios', RegulatoryRatiosController::class);
    Route::resource('liquidityratio', LiquidityController::class);

    // API Routes to fetch data
   Route::get('/api/budget-activities', [BudgetActivitiesController::class, 'fetchActivities'])->name('api.budget-activities');

    // Business Intelligence & Deep Analytics
    Route::resource('analyticsdashboard', BusinessAnalyticsDashboardController::class);
    Route::resource('kpidashboards', KPIDashboardsController::class);
    Route::resource('trendsdashboards', TrendAndGrowthController::class);

    Route::resource('branchperformance', BranchPerformanceController::class);
    Route::resource('productprofitability', ProductProfitabilityController::class);
    Route::resource('officerperformance', OfficerPerformanceController::class);
    Route::resource('loanbooktrends', LoanBookTrendsController::class);
    Route::resource('depositbooktrends', DepositBookTrendsController::class);
    Route::resource('topdepositors', TopDepositorsController::class);
    Route::resource('toploans', TopLoansController::class);
    Route::resource('dormantcasa', DormantCASAController::class);
    Route::resource('nplrisk', NPLRiskController::class);
    Route::resource('eclprovisioning', ECLProvisioningController::class);
    Route::resource('cbkratios', CBKRegulatoryRatiosController::class);
    Route::resource('topcurrentaccounts', TopCurrentAccountsController::class);
    Route::resource('topsavingaccounts', TopSavingAccountsController::class);
    Route::resource('capitaladequacyratio', CapitalAdequacyController::class);
    Route::resource('loantodepositratio', LoanToDepositController::class);
    Route::resource('costtoincomeratio', CostToIncomeController::class);
    Route::resource('returnonassetsratio', ReturnOnAssetsController::class);
    Route::resource('returnonequityratio', ReturnOnEquityController::class);
    Route::resource('multidimensional', MultidimensionalStatisticsController::class);
    Route::resource('incomebybranch', IncomeByBranchStatisticsController::class);
    Route::resource('expensebygl', ExpenseByGLStatisticsController::class);
    Route::resource('npltrendbyproduct', NPLTrendByProductController::class);
    Route::resource('depositgrowthbyofficer', DepositGrowthByOfficerController::class);
    Route::resource('budgetvsactualbybranch', BudgetvsActualbyBranchController::class);
    Route::resource('loanyieldbybranch', LoanYieldbyProductController::class);
    Route::resource('analyticsdataexport', DataExportToolsProductController::class);


    // Admin & Integration
    Route::resource('cbssync', CBSSyncController::class);
    Route::resource('datasynclogs', DataSyncLogsController::class);

    //Budget Settings
    Route::resource('rates', BudgetRatesController::class);
    Route::resource('planningmethods', BudgetPlanningMethodsController::class);
    Route::resource('periodtypes', BudgetPeriodTypesController::class);

    Route::resource('budgetdriverssetup',BudgetDriversSetupController::class);
    Route::get('budgetlinemapping/gl-subtypes/{typeId}', [BudgetLineMappingController::class, 'getGLAccountSubTypes']);

    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('budget-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'budget-reports.index',
        'show' => 'budget-reports.show'
    ]);
});
