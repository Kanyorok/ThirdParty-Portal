<?php

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
use App\Http\Controllers\Property\PropertyRegistryController;
use App\Http\Controllers\Property\PropertyReportsController;
use App\Http\Controllers\Property\PropertyReportsVisualController;
use App\Http\Controllers\Property\PropertyTenantClearanceController;
use App\Http\Controllers\Property\PropertyTypeController;
use App\Http\Controllers\Property\PropertyUnitController;
use App\Http\Controllers\Property\RentDashboardController;
use App\Http\Controllers\Property\ReportsController;
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
    Route::get('propertyregistry/index', [PropertyRegistryController::class,'index'])->name('PropertyRegistry.index');
    Route::get('propertyregistry/create', [PropertyRegistryController::class,'create'])->name('PropertyRegistry.create');
    Route::post('propertyregistry', [PropertyRegistryController::class,'store'])->name('propertyregistry.store');
    Route::get('propertyregistry/show/{id}', [PropertyRegistryController::class,'show'])->name('PropertyRegistry.show');
    Route::get('/propertyregistry/types/{categoryId}', [PropertyRegistryController::class, 'getTypesByCategory'])->name('gettypes');
    Route::delete('propertyregistry/delete/{Id}', [PropertyRegistryController::class,'destroy'])->name('PropertyRegistry.destroy');
    Route::get('propertyregistry/edit/{Id}',[PropertyRegistryController::class,'edit'])->name('PropertyRegistry.edit');
    Route::put('propertyregistry/edit/{Id}',[PropertyRegistryController::class,'update'])->name('PropertyRegistry.update');



    //Property Block
    //Route::resource('addblock', PropertyBlockController::class);
    Route::get('propertyaddblock', [PropertyBlockController::class,'index'])->name('addblock.index');
    Route::get('propertyaddblock/create', [PropertyBlockController::class,'create'])->name('addblock.create');
    Route::post('propertyaddblock', [PropertyBlockController::class,'store'])->name('addblock.store');
    Route::get('propertyaddblock/show', [PropertyBlockController::class,'show'])->name('addblock.show');
    Route::delete('propertyaddblock/delete/{Id}', [PropertyBlockController::class,'destroy'])->name('addblock.destroy');
    Route::get('propertyaddblock/edit/{Id}',[PropertyBlockController::class,'edit'])->name('addblock.edit');
    Route::put('propertyaddblock/update/{Id}',[PropertyBlockController::class,'update'])->name('addblock.update');



    //Property Settings
    Route::resource('propertysettings', PropertyUnitController::class);
    //Route::get('propertysettings', [PropertyRegistryController::class,'index'])->name('propertysettings.index');

    //Route::resource('addfloor', PropertyFloorController::class);
    Route::get('propertyaddfloor', [PropertyFloorController::class,'index'])->name('addfloor.index');
    Route::get('propertyaddfloor/create', [PropertyFloorController::class,'create'])->name('addfloor.create');
    Route::post('propertyaddfloor', [PropertyFloorController::class,'store'])->name('addfloor.store');
    Route::get('propertyaddfloor/show/', [PropertyFloorController::class,'show'])->name('addfloor.show');
    Route::get('/propertyaddfloor/{BlockId}', [PropertyFloorController::class, 'getBlockByProperty'])->name('getblockbyproperty');
    Route::delete('propertyaddfloor/delete/{Id}', [PropertyFloorController::class,'destroy'])->name('addfloor.destroy');
    Route::get('propertyaddfloor/edit/{Id}',[PropertyFloorController::class,'edit'])->name('addfloor.edit');
    Route::put('propertyaddfloor/edit/{Id}',[PropertyFloorController::class,'update'])->name('addfloor.update');


    //Route::resource('addunit', PropertyUnitController::class);
    Route::get('propertyaddunit', [PropertyUnitController::class,'index'])->name('addunit.index');
    Route::get('propertyaddunit/create', [PropertyUnitController::class,'create'])->name('addunit.create');
    Route::post('propertyaddunit', [PropertyUnitController::class,'store'])->name('addunit.store');
    Route::get('propertyaddunit/show', [PropertyUnitController::class,'show'])->name('addunit.show');
    Route::get('/propertyaddunit/blocks/{PropertyId}', [PropertyUnitController::class, 'getBlockByProperty'])->name('getblockbyproperty');
    Route::get('/propertyaddunit/floors/{BlockId}', [PropertyUnitController::class, 'getFloorByBlock'])->name('getfloorbyblock');
    Route::delete('propertyaddunit/delete/{Id}', [PropertyUnitController::class,'destroy'])->name('addunit.destroy');
    Route::get('propertyaddunit/edit/{Id}',[PropertyUnitController::class,'edit'])->name('addunit.edit');
    Route::put('propertyaddunit/edit/{Id}',[PropertyUnitController::class,'update'])->name('addunit.update');


    //Route::resource('addtenant', PropertyNewTenantController::class);
    Route::get('propertyaddtenant', [PropertyNewTenantController::class,'index'])->name('addtenant.index');
    Route::get('propertyaddtenant/create', [PropertyNewTenantController::class,'create'])->name('addtenant.create');
    Route::post('propertyaddtenant', [PropertyNewTenantController::class,'store'])->name('addtenant.store');
    Route::get('propertyaddtenant/show/{id}', [PropertyNewTenantController::class,'show'])->name('addtenant.show');
    Route::get('propertyaddtenant/edit/{Id}',[PropertyNewTenantController::class,'edit'])->name('addtenant.edit');
    Route::put('propertyaddtenant/edit/{Id}',[PropertyNewTenantController::class,'update'])->name('addtenant.update'); 

    
    //Route::resource('tenantclearance', PropertyTenantClearanceController::class);
    Route::get('propertytenantclearance', [PropertyTenantClearanceController::class,'index'])->name('tenantclearance.index');
    Route::get('propertytenantclearance/create', [PropertyTenantClearanceController::class,'create'])->name('tenantclearance.create');
    Route::post('propertytenantclearance', [PropertyTenantClearanceController::class,'store'])->name('tenantclearance.store');
    Route::get('propertytenantclearance/show/{Id}', [PropertyTenantClearanceController::class,'show'])->name('tenantclearance.show');
    Route::get('propertytenantclearance/edit/{Id}',[PropertyTenantClearanceController::class,'edit'])->name('tenantclearance.edit');
    Route::put('propertytenantclearance/edit/{Id}',[PropertyTenantClearanceController::class,'update'])->name('tenantclearance.update');
    Route::delete('propertytenantclearance/delete/{Id}', [PropertyTenantClearanceController::class,'destroy'])->name('tenantclearance.destroy'); 


    //Route::resource('addlease', PropertyNewLeaseController::class);
    Route::get('propertyaddlease', [PropertyNewLeaseController::class,'index'])->name('addlease.index');
    Route::get('propertyaddlease/create', [PropertyNewLeaseController::class,'create'])->name('addlease.create');
    Route::post('propertyaddlease', [PropertyNewLeaseController::class,'store'])->name('addlease.store');
    Route::get('propertyaddlease/show/{Id}', [PropertyNewLeaseController::class,'show'])->name('addlease.show');
    Route::get('propertyaddlease/edit/{Id}',[PropertyNewLeaseController::class,'edit'])->name('addlease.edit');
    Route::put('propertyaddlease/edit/{Id}',[PropertyNewLeaseController::class,'update'])->name('addlease.update');
    Route::delete('propertyaddlease/delete/{Id}', [PropertyNewLeaseController::class,'destroy'])->name('addlease.destroy');
    Route::get('/propertyaddlease/blocks/{PropertyId}', [PropertyNewLeaseController::class, 'getBlockByProperty'])->name('getblockbyproperty');
    Route::get('/propertyaddlease/floors/{BlockId}', [PropertyNewLeaseController::class, 'getFloorByBlock'])->name('getfloorbyblock');
    Route::get('/propertyaddlease/Units/{FloorId}', [PropertyNewLeaseController::class, 'getUnitByFloor'])->name('getunitbyfloor');
    

    //Route::resource('terminatelease', PropertyLeaseTerminationController::class);
    Route::get('propertyterminatelease', [PropertyLeaseTerminationController::class,'index'])->name('terminatelease.index');
    Route::get('propertyterminatelease/create', [PropertyLeaseTerminationController::class,'create'])->name('terminatelease.create');
    Route::post('propertyterminatelease', [PropertyLeaseTerminationController::class,'store'])->name('terminatelease.store');
    Route::get('propertyterminatelease/show/{Id}', [PropertyLeaseTerminationController::class,'show'])->name('terminatelease.show');
    Route::get('propertyterminatelease/edit/{Id}',[PropertyLeaseTerminationController::class,'edit'])->name('terminatelease.edit');
    Route::put('propertyterminatelease/edit/{Id}',[PropertyLeaseTerminationController::class,'update'])->name('terminatelease.update');



    //Route::resource('schedulelease', PropertyLeaseScheduleController::class);
    Route::get('schedulelease', [PropertyLeaseScheduleController::class,'index'])->name('schedulelease.index');
    Route::get('schedulelease/create', [PropertyLeaseScheduleController::class,'create'])->name('schedulelease.create');
    Route::post('schedulelease', [PropertyLeaseScheduleController::class,'store'])->name('schedulelease.store');
    Route::get('schedulelease/property/{Id}', [PropertyLeaseScheduleController::class, 'getPropertyByTenant'])->name('getpropertybytenant');
    Route::get('schedulelease/lease/{Id}', [PropertyLeaseScheduleController::class, 'getLeaseByProperty'])->name('getleasebyproperty');
    Route::get('schedulelease/show/{id}', [PropertyLeaseScheduleController::class,'show'])->name('schedulelease.show');
    Route::get('schedulelease/edit/{Id}',[PropertyLeaseScheduleController::class,'edit'])->name('schedulelease.edit');
    Route::put('schedulelease/edit/{Id}',[PropertyLeaseScheduleController::class,'update'])->name('schedulelease.update');
    Route::delete('schedulelease/delete/{Id}', [PropertyLeaseScheduleController::class,'destroy'])->name('schedulelease.destroy');

    
    Route::resource('attachments', PropertyAttachmentsController::class);
    Route::resource('renewlease', PropertyLeaseRenewalController::class);
    Route::resource('rentinvoice', PropertyInvoiceController::class);
    Route::resource('rentreceipt', PropertyReceiptController::class);
    Route::resource('rentdashboard', RentDashboardController::class);
    Route::resource('tenantledger', TenantStatementController::class);
    Route::resource('maintenancerequest', PropertyMaintenanceRequestController::class);
    Route::resource('assignrequest', PropertyMaintananceAssignController::class);
    Route::resource('maintenancedashboard', PropertyMaintenanceDashboardController::class);
    Route::resource('workcompletion', PropertyMaintenanceWorkCompletionController::class);
    Route::resource('propertyreports', PropertyReportsController::class);
    Route::resource('propertyanalytics', PropertyReportsVisualController::class);


    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('property-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'property-reports.index',
        'show' => 'property-reports.show'
    ]);
});
