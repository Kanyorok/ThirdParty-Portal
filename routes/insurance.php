<?php

use App\Http\Controllers\Insurance\ClaimsManagementController;
use App\Http\Controllers\Insurance\CoveredassetManagementController;
use App\Http\Controllers\Insurance\InsurancepolicyManagementController;
use App\Http\Controllers\Insurance\InsurancetypeManagementController;
use App\Http\Controllers\Insurance\PremiumpaymentManagementController;
use App\Http\Controllers\Insurance\ProviderManagementController;
use App\Http\Controllers\Insurance\RenewalManagementController;
use App\Http\Controllers\Insurance\ReportManagementController;
use Illuminate\Support\Facades\Route;


Route::namespace('Insurance')->prefix('insurance')->group(function () {
    Route::resource('providermanagement', ProviderManagementController::class);
    Route::resource('insurancetypemanagement', InsurancetypeManagementController::class);
    Route::resource('insurancepolicymanagement', InsurancepolicyManagementController::class);
    Route::resource('coveredassetmanagement', CoveredassetManagementController::class);
    Route::resource('premiumpaymentmanagement', PremiumpaymentManagementController::class);
    Route::resource('claimsmanagement', ClaimsManagementController::class);
    Route::resource('renewalmanagement', RenewalManagementController::class);
    Route::resource('reportmanagement', ReportManagementController::class);


});
