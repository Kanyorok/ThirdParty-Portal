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
use App\Http\Controllers\Budget\BudgetLineLedgerLimitController;
use App\Http\Controllers\Budget\BudgetReallocationController;
use Illuminate\Support\Facades\Route;


Route::middleware(['module:1200000'])->namespace('Budget')->prefix('budget')->group(function () {
    Route::resource('budgetline', BudgetLinesController::class);
    Route::resource('budgetperiod', BudgetPeriodController::class);
    Route::post('/budgetperiod/attachGL', [BudgetPeriodController::class, 'attachGL'])->name('budgetperiod.attachGL');;
    Route::resource('budgetproductmaster', BudgetProductMasterController::class);
    Route::resource('budgetproducttype', BudgetProductTypeController::class);
    Route::resource('budgetlinemapping', BudgetLineMappingController::class);
    Route::delete('/delete-lineProduct/{id}', [BudgetLineMappingController::class, 'destroyProduct'])->name('budgetlinemapping.destroyProduct');
    Route::resource('budgetglmapping', BudgetGLMappingController::class);
    Route::resource('budgetdrivers', BudgetDriversController::class);
    Route::resource('budgetlinecategories', BudgetLineCategoriesController::class);
    Route::resource('budgetactivities', BudgetActivitiesController::class);
    Route::resource('yieldexpenserate', YieldRateController::class);
    //Route::resource('budgetprojections', BudgetProjectionsController::class);
    Route::resource('budgetprojections', BudgetProjectionsController::class);
    Route::post('/storeProjections', [BudgetProjectionsController::class, 'storeProjections'])->name('budgetprojections.storeProjections');
    Route::post('/deleteProjection', [BudgetProjectionsController::class, 'deleteProjection'])->name('budgetprojections.deleteProjection.post');
    Route::get('/budget-lines/{id}/product-types', [BudgetProjectionsController::class, 'getProductTypes'])->name('budget-lines.product-types');
    Route::resource('entrybyglline', BudgetGLLineEntryController::class);
    Route::get('/entrybyglline/glview/{budgetId}', [BudgetGLLineEntryController::class, 'glview'])->name('entrybyglline.glview');
    Route::resource('submitapproval', BudgetSubmitController::class);
    Route::resource('budgetapproval', BudgetApprovalController::class);
    Route::post('/budgetapproval/approve', [BudgetApprovalController::class, 'approve'])->name('budgetapproval.approve');
    Route::post('/budgetapproval/reject', [BudgetApprovalController::class, 'reject'])->name('budgetapproval.reject');
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

    Route::resource('budgetdriverssetup', BudgetDriversSetupController::class);
    Route::get('budgetlinemapping/gl-subtypes/{typeId}', [BudgetLineMappingController::class, 'getGLAccountSubTypes']);
    Route::get('budgetlinemapping/gl-types/{typeId}', [BudgetLineMappingController::class, 'getGLTypes']);

    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('budgetline-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'budgetline-reports.index',
        'show' => 'budgetline-reports.show'
    ]);

    Route::delete('/budget/deleteProjection/{id}', [BudgetProjectionsController::class, 'deleteProjection'])->name('budgetprojections.deleteProjection');
    Route::delete('budget/delete-gl-attachment/{id}', [BudgetPeriodController::class, 'delGLAttachment'])->name('budget.delete-gl-attachment');
    Route::delete('budget/delete-budget/{id}', [BudgetPeriodController::class, 'delBudget'])->name('budget.delete-budget');
});


Route::middleware(['module:1200000'])->prefix('budgetandanalytics/limits')->name('budgetandanalytics.limits.')->group(function () {
    Route::get('/', [BudgetLineLedgerLimitController::class, 'index'])->name('index');
    Route::get('/create', [BudgetLineLedgerLimitController::class, 'create'])->name('create');
    Route::post('/store', [BudgetLineLedgerLimitController::class, 'store'])->name('store');

    Route::get('/showLimits', [BudgetLineLedgerLimitController::class, 'showUpdateForm'])->name('showUpdateForm');
    Route::post('/updateLimits', [BudgetLineLedgerLimitController::class, 'runUpdate'])->name('runUpdate');

    // optional future routes
    Route::get('/{id}/edit', [BudgetLineLedgerLimitController::class, 'edit'])->name('edit');
    Route::post('/{id}/update', [BudgetLineLedgerLimitController::class, 'update'])->name('update');
    Route::post('/{id}/delete', [BudgetLineLedgerLimitController::class, 'destroy'])->name('destroy');
});

Route::middleware(['module:1200000'])->prefix('budgetandanalytics/reallocation')
    ->name('budgetandanalytics.reallocation.')
    ->group(function () {
        Route::get('/', [BudgetReallocationController::class, 'index'])->name('index');
        Route::get('/create', [BudgetReallocationController::class, 'create'])->name('create');
        Route::post('/store', [BudgetReallocationController::class, 'store'])->name('store');
        Route::get('/allocate', [BudgetReallocationController::class, 'allocate'])->name('allocate');

        // Optional future routes
        Route::get('/{id}/review', [BudgetReallocationController::class, 'review'])->name('review');
        Route::post('/{id}/approve', [BudgetReallocationController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [BudgetReallocationController::class, 'reject'])->name('reject');

        // ✅ AJAX routes (unique names; paths match your JS)
        Route::get('/budget-lines/{deptId}/{branchId?}', [BudgetReallocationController::class, 'getBudgetLines'])
            ->name('budget-lines'); // name used ONLY here

        Route::get('/budget-line/{lineId}/details/{branchId?}', [BudgetReallocationController::class, 'getBudgetLineDetails'])
            ->name('budget-line.details');

        // ✅ Allocations endpoint expected by your fetch()
        Route::post('/allocations', [BudgetReallocationController::class, 'getAllocations'])
            ->name('allocations');
    });

// Budget Approvals
Route::middleware(['module:1200000'])->prefix('budgetandanalytics/budget-approvals')->name('budgetandanalytics.budget-approvals.')->group(function () {
    Route::get('/approve', [BudgetPeriodController::class, 'approve'])->name('approve');
    Route::get('/reject', [BudgetPeriodController::class, 'reject'])->name('reject');
});

//Fetch Data for modal in the reallocation index
Route::middleware(['module:1200000'])->get('/budgetandanalytics/reallocation/{id}/details', [BudgetReallocationController::class, 'getDetails'])->name('budgetandanalytics.reallocation.details');
