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

    //category
    Route::get('propercategory', [PropertyCategoryController::class,'index'])->name('propertycategory.index');
    Route::get('propercategory/create', [PropertyCategoryController::class,'create'])->name('propertycategory.create');
    Route::post('propercategory', [PropertyCategoryController::class,'store'])->name('propertycategory.store');
    Route::delete('propercategory/delete/{Id}', [PropertyCategoryController::class,'destroy'])->name('propertycategory.destroy');
    Route::get('propertycategory/edit/{Id}',[PropertyCategoryController::class,'edit'])->name('propertycategories.edit');
    Route::put('propertycategory/edit/{Id}',[PropertyCategoryController::class,'update'])->name('propertycategories.update');

    //Type
    Route::get('propertytype', [PropertyTypeController::class,'index'])->name('propertytype.index');
    Route::get('propertytype/create', [PropertyTypeController::class,'create'])->name('propertytype.create');
    Route::post('propertytype', [PropertyTypeController::class,'store'])->name('propertytype.store');
    Route::delete('propertytype/delete/{Id}', [PropertyTypeController::class,'destroy'])->name('propertytype.destroy');
    Route::get('propertytype/edit/{Id}',[PropertyTypeController::class,'edit'])->name('propertytype.edit');
    Route::put('propertytype/edit/{Id}',[PropertyTypeController::class,'update'])->name('propertytype.update');


    //Property Registry
    Route::get('propertyregistry', [PropertyRegistryController::class,'index'])->name('PropertyRegistry.index');
    Route::get('propertyregistry/create', [PropertyRegistryController::class,'create'])->name('PropertyRegistry.create');
    Route::post('propertyregistry', [PropertyRegistryController::class,'store'])->name('propertyregistry.store');
    Route::get('propertyregistry/show', [PropertyRegistryController::class,'show'])->name('PropertyRegistry.show');
    Route::get('/propertyregistry/types/{categoryId}', [PropertyRegistryController::class, 'getTypesByCategory'])->name('gettypes');


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
