<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Budget\BudgetItemsController;
use App\Http\Controllers\Budget\BudgetPeriodController;
use App\Http\Controllers\Budget\BudgetProductMasterController;
use App\Http\Controllers\Budget\BudgetProductTypeController; 
use App\Http\Controllers\Budget\BudgetLineMappingController;
use App\Http\Controllers\Budget\BudgetGLMappingController;
use App\Http\Controllers\Budget\BudgetDriversController; 
use App\Http\Controllers\Budget\BudgetProductEntryController; 
use App\Http\Controllers\Budget\BudgetGLLineEntryController;
use App\Http\Controllers\Budget\BudgetSubmitController;
use App\Http\Controllers\Budget\BudgetApprovalController;
use App\Http\Controllers\Budget\BudgetTopDownAllocationController;
use App\Http\Controllers\Budget\BudgetSceneriosController;
use App\Http\Controllers\Budget\BudgetFormulaController;
use App\Http\Controllers\Budget\BudgetConsolidationController;  
use App\Http\Controllers\Budget\BudgetvsActualDashboardController; 
use App\Http\Controllers\Budget\BudgetVarianceAnalysisController; 
use App\Http\Controllers\Budget\BudgetKPIscorecardsController; 



Route::namespace('Budget')->group(function () {
    Route::resource('budgetline', BudgetLinesController::class);
    Route::resource('budgetperiod', BudgetPeriodController::class);
    Route::resource('budgetproductmaster', BudgetProductMasterController::class);
    Route::resource('budgetproducttype', BudgetProductTypeController::class);
    Route::resource('budgetlinemapping', BudgetLineMappingController::class);
    Route::resource('budgetglmapping', BudgetGLMappingController::class);
    Route::resource('budgetdrivers', BudgetDriversController::class);
    Route::resource('entrybyproduct', BudgetProductEntryController::class);
    Route::resource('entrybyglline', BudgetGLLineEntryController::class);
    Route::resource('submitapproval', BudgetSubmitController::class);
    Route::resource('budgetapproval', BudgetApprovalController::class);
    Route::resource('topdownallocation', BudgetTopDownAllocationController::class);
    Route::resource('budgetscenerios', BudgetSceneriosController::class);
    Route::resource('budgetformula', BudgetFormulaController::class);
    Route::resource('budgetconsolidation', BudgetConsolidationController::class);
    Route::resource('budgetvsactualdashboard', BudgetvsActualDashboardController::class);
    Route::resource('budgetvarianceanalysis', BudgetVarianceAnalysisController::class);
    Route::resource('kpiscorecards', BudgetKPIscorecardsController::class);
    
    


});
