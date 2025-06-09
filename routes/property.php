<?php

use App\Http\Controllers\Property\PropertyRegistryController;
use App\Http\Controllers\Property\PropertyAttachmentsController;
use App\Http\Controllers\Property\PropertyBlockController;
use App\Http\Controllers\Property\PropertyCategoryController;
use App\Http\Controllers\Property\PropertyFloorController;
use App\Http\Controllers\Property\PropertyInvoiceController;
use App\Http\Controllers\Property\PropertyLeaseRenewalController;
use App\Http\Controllers\Property\PropertyLeaseScheduleController;
use App\Http\Controllers\Property\PropertyLeaseTerminationController;
use App\Http\Controllers\Property\PropertyMaintananceAssignController;
use App\Http\Controllers\Property\PropertyMaintenanceDashboardController;
use App\Http\Controllers\Property\PropertyMaintenanceRequestController;
use App\Http\Controllers\Property\PropertyMaintenanceWorkCompletionController;
use App\Http\Controllers\Property\PropertyNewLeaseController;
use App\Http\Controllers\Property\PropertyNewTenantController;
use App\Http\Controllers\Property\PropertyPaymentFrequencyController;
use App\Http\Controllers\Property\PropertyReceiptController;
use App\Http\Controllers\Property\PropertyReportsController;
use App\Http\Controllers\Property\PropertyReportsVisualController;
use App\Http\Controllers\Property\PropertyTenantClearanceController;
use App\Http\Controllers\Property\PropertyTypeController;
use App\Http\Controllers\Property\PropertyUnitController;
use App\Http\Controllers\Property\RentDashboardController;
use App\Http\Controllers\Property\TenantStatementController;
use Illuminate\Support\Facades\Route;


Route::namespace('Property')->prefix('property')->group(function () {
    Route::post('propertytype', [PropertyTypeController::class,'store'])->name('propertytype.store');
    Route::post('propercategory', [PropertyCategoryController::class,'store'])->name('propercategory.store');
    Route::post('propertyregistry', [PropertyRegistryController::class,'store'])->name('propertyregistry.store');
    Route::resource('PropertyRegistry', PropertyRegistryController::class);
    Route::resource('propertytype', PropertyTypeController::class);
    Route::resource('propertycategory', PropertyCategoryController::class);
    Route::resource('addblock', PropertyBlockController::class);
    Route::resource('addfloor', PropertyFloorController::class);
    Route::resource('addunit', PropertyUnitController::class);
    Route::resource('attachments', PropertyAttachmentsController::class);
    Route::resource('addtenant', PropertyNewTenantController::class);
    Route::resource('addlease', PropertyNewLeaseController::class);
    Route::resource('schedulelease', PropertyLeaseScheduleController::class);
    Route::resource('renewlease', PropertyLeaseRenewalController::class);
    Route::resource('terminatelease', PropertyLeaseTerminationController::class);
    Route::resource('paymentfrequency', PropertyPaymentFrequencyController::class);
    Route::resource('rentinvoice', PropertyInvoiceController::class);
    Route::resource('rentreceipt', PropertyReceiptController::class);
    Route::resource('rentdashboard', RentDashboardController::class);
    Route::resource('tenantledger', TenantStatementController::class);
    Route::resource('tenantclearance', PropertyTenantClearanceController::class);
    Route::resource('maintenancerequest', PropertyMaintenanceRequestController::class);
    Route::resource('assignrequest', PropertyMaintananceAssignController::class);
    Route::resource('maintenancedashboard', PropertyMaintenanceDashboardController::class);
    Route::resource('workcompletion', PropertyMaintenanceWorkCompletionController::class);
    Route::resource('propertyreports', PropertyReportsController::class);
    Route::resource('propertyanalytics', PropertyReportsVisualController::class);


});
