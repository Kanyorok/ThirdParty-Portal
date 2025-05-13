<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legal\CaseregisterController;
use App\Http\Controllers\Legal\CasedetailsController;
use App\Http\Controllers\Legal\HearingController;
use App\Http\Controllers\Legal\CasedocumentsController;
use App\Http\Controllers\Legal\CasenotesController;
use App\Http\Controllers\Legal\ContractapprovalController;
use App\Http\Controllers\Legal\ContractdraftingController;
use App\Http\Controllers\Legal\ContractrepositoryController;
use App\Http\Controllers\Legal\ObligationtrackerController;
use App\Http\Controllers\Legal\RenewalsController;
use App\Http\Controllers\Legal\RegulatorychecklistController;
use App\Http\Controllers\Legal\CompliancecalendarController;
use App\Http\Controllers\Legal\FillingtrackerController;
use App\Http\Controllers\Legal\NoncomplainceregisterController;
use App\Http\Controllers\Legal\CasessummaryController;
use App\Http\Controllers\Legal\LegalexpensesController;
use App\Http\Controllers\Legal\HearingsController;


Route::namespace('Legal')->group(function () {
    Route::resource('caseregister', CaseregisterController::class);
    Route::resource('casedetails', CasedetailsController::class);
    Route::resource('hearing', HearingController::class);
    Route::resource('casedocuments', CasedocumentsController::class);
    Route::resource('casenotes', CasenotesController::class);
    Route::resource('contractapproval', ContractapprovalController::class);
    Route::resource('contractdrafting', ContractdraftingController::class);
    Route::resource('contractrepository', ContractrepositoryController::class);
    Route::resource('obligationtracker', ObligationtrackerController::class);
    Route::resource('renewals', RenewalsController::class);
    Route::resource('regulatorychecklist', RegulatorychecklistController::class);
    Route::resource('compliancecalendar', CompliancecalendarController::class);
    Route::resource('fillingtracker', FillingtrackerController::class);
    Route::resource('noncomplianceregister', NoncomplianceregisterController::class);
    Route::resource('casessummary', CasessummaryController::class);
    Route::resource('legalexpenses', LegalexpensesController::class);
    Route::resource('hearings', HearingsController::class);
     
    
});