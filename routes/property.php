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
use App\Http\Controllers\Property\PropertyRegistryController;
use App\Http\Controllers\Property\PropertyReportsController;
use App\Http\Controllers\Property\PropertyReportsVisualController;
use App\Http\Controllers\Property\PropertyTenantClearanceController;
use App\Http\Controllers\Property\PropertyTypeController;
use App\Http\Controllers\Property\PropertyUnitController;
use App\Http\Controllers\Property\RentDashboardController;
use App\Http\Controllers\Property\ReportsController;
use App\Http\Controllers\Property\TenantStatementController;
use App\Http\Controllers\Property\PropertyReceiptController;

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
    Route::delete('propertytype/delete/{Id}', [PropertyTypeController::class, 'destroy'])->name('propertytype.destroy');
    Route::get('propertytype/edit/{Id}', [PropertyTypeController::class, 'edit'])->name('propertytype.edit');
    Route::put('propertytype/edit/{Id}', [PropertyTypeController::class, 'update'])->name('propertytype.update');


    //Property Registry
    Route::get('propertyregistry/index', [PropertyRegistryController::class, 'index'])->name('PropertyRegistry.index');
    Route::get('propertyregistry/create', [PropertyRegistryController::class,'create'])->name('PropertyRegistry.create');
    Route::post('propertyregistry', [PropertyRegistryController::class,'store'])->name('propertyregistry.store');
    Route::get('propertyregistry/show/{id}', [PropertyRegistryController::class, 'show'])->name('PropertyRegistry.show');
    Route::get('/propertyregistry/types/{categoryId}', [PropertyRegistryController::class, 'getTypesByCategory'])->name('gettypes');
    Route::delete('propertyregistry/delete/{Id}', [PropertyRegistryController::class, 'destroy'])->name('PropertyRegistry.destroy');
    Route::get('propertyregistry/edit/{Id}', [PropertyRegistryController::class, 'edit'])->name('PropertyRegistry.edit');
    Route::put('propertyregistry/edit/{Id}', [PropertyRegistryController::class, 'update'])->name('PropertyRegistry.update');

    //Route::resource('attachments', PropertyAttachmentsController::class);
    Route::get('attachments', [PropertyAttachmentsController::class,'index'])->name('attachments.index');
    Route::get('attachments/create', [PropertyAttachmentsController::class,'create'])->name('attachments.create');
    Route::post('attachments', [PropertyAttachmentsController::class,'store'])->name('attachments.store');
    Route::get('attachments/show/{Id}', [PropertyAttachmentsController::class,'show'])->name('attachments.show');
    Route::get('attachments/edit/{Id}',[PropertyAttachmentsController::class,'edit'])->name('attachments.edit');
    Route::put('attachments/edit/{Id}',[PropertyAttachmentsController::class,'update'])->name('attachments.update');
    Route::delete('attachments/delete/{Id}', [PropertyAttachmentsController::class,'destroy'])->name('attachments.destroy');

    //Property Block
    //Route::resource('addblock', PropertyBlockController::class);
    Route::get('propertyaddblock', [PropertyBlockController::class, 'index'])->name('addblock.index');
    Route::get('propertyaddblock/create', [PropertyBlockController::class, 'create'])->name('addblock.create');
    Route::post('propertyaddblock', [PropertyBlockController::class, 'store'])->name('addblock.store');
    Route::get('propertyaddblock/show', [PropertyBlockController::class, 'show'])->name('addblock.show');
    Route::delete('propertyaddblock/delete/{Id}', [PropertyBlockController::class, 'destroy'])->name('addblock.destroy');
    Route::get('propertyaddblock/edit/{Id}', [PropertyBlockController::class, 'edit'])->name('addblock.edit');
    Route::put('propertyaddblock/update/{Id}', [PropertyBlockController::class, 'update'])->name('addblock.update');


    //Property Settings
    Route::resource('propertysettings', PropertyUnitController::class);
    //Route::get('propertysettings', [PropertyRegistryController::class,'index'])->name('propertysettings.index');

    //Route::resource('addfloor', PropertyFloorController::class);
    Route::get('propertyaddfloor', [PropertyFloorController::class, 'index'])->name('addfloor.index');
    Route::get('propertyaddfloor/create', [PropertyFloorController::class, 'create'])->name('addfloor.create');
    Route::post('propertyaddfloor', [PropertyFloorController::class, 'store'])->name('addfloor.store');
    Route::get('propertyaddfloor/show/', [PropertyFloorController::class, 'show'])->name('addfloor.show');
    Route::get('/propertyaddfloor/{BlockId}', [PropertyFloorController::class, 'getBlockByProperty'])->name('getblockbyproperty');
    Route::delete('propertyaddfloor/delete/{Id}', [PropertyFloorController::class, 'destroy'])->name('addfloor.destroy');
    Route::get('propertyaddfloor/edit/{Id}', [PropertyFloorController::class, 'edit'])->name('addfloor.edit');
    Route::put('propertyaddfloor/edit/{Id}', [PropertyFloorController::class, 'update'])->name('addfloor.update');


    //Route::resource('addunit', PropertyUnitController::class);
    Route::get('propertyaddunit', [PropertyUnitController::class, 'index'])->name('addunit.index');
    Route::get('propertyaddunit/create', [PropertyUnitController::class, 'create'])->name('addunit.create');
    Route::post('propertyaddunit', [PropertyUnitController::class, 'store'])->name('addunit.store');
    Route::get('propertyaddunit/show', [PropertyUnitController::class, 'show'])->name('addunit.show');
    Route::get('/propertyaddunit/blocks/{PropertyId}', [PropertyUnitController::class, 'getBlockByProperty'])->name('getblockbyproperty');
    Route::get('/propertyaddunit/floors/{BlockId}', [PropertyUnitController::class, 'getFloorByBlock'])->name('getfloorbyblock');
    Route::delete('propertyaddunit/delete/{Id}', [PropertyUnitController::class, 'destroy'])->name('addunit.destroy');
    Route::get('propertyaddunit/edit/{Id}', [PropertyUnitController::class, 'edit'])->name('addunit.edit');
    Route::put('propertyaddunit/edit/{Id}', [PropertyUnitController::class, 'update'])->name('addunit.update');


    //Route::resource('addtenant', PropertyNewTenantController::class);
    Route::get('propertyaddtenant', [PropertyNewTenantController::class, 'index'])->name('addtenant.index');
    Route::get('propertyaddtenant/create', [PropertyNewTenantController::class, 'create'])->name('addtenant.create');
    Route::post('propertyaddtenant', [PropertyNewTenantController::class, 'store'])->name('addtenant.store');
    Route::get('propertyaddtenant/show/{id}', [PropertyNewTenantController::class, 'show'])->name('addtenant.show');
    Route::get('propertyaddtenant/edit/{Id}', [PropertyNewTenantController::class, 'edit'])->name('addtenant.edit');
    Route::put('propertyaddtenant/edit/{Id}', [PropertyNewTenantController::class, 'update'])->name('addtenant.update');


    //Route::resource('tenantclearance', PropertyTenantClearanceController::class);
    Route::get('propertytenantclearance', [PropertyTenantClearanceController::class, 'index'])->name('tenantclearance.index');
    Route::get('propertytenantclearance/create', [PropertyTenantClearanceController::class, 'create'])->name('tenantclearance.create');
    Route::post('propertytenantclearance', [PropertyTenantClearanceController::class, 'store'])->name('tenantclearance.store');
    Route::get('propertytenantclearance/show/{Id}', [PropertyTenantClearanceController::class, 'show'])->name('tenantclearance.show');
    Route::get('propertytenantclearance/edit/{Id}', [PropertyTenantClearanceController::class, 'edit'])->name('tenantclearance.edit');
    Route::put('propertytenantclearance/edit/{Id}', [PropertyTenantClearanceController::class, 'update'])->name('tenantclearance.update');
    Route::delete('propertytenantclearance/delete/{Id}', [PropertyTenantClearanceController::class, 'destroy'])->name('tenantclearance.destroy');


    //Route::resource('addlease', PropertyNewLeaseController::class);
    Route::get('propertyaddlease', [PropertyNewLeaseController::class, 'index'])->name('addlease.index');
    Route::get('propertyaddlease/create', [PropertyNewLeaseController::class, 'create'])->name('addlease.create');
    Route::post('propertyaddlease', [PropertyNewLeaseController::class, 'store'])->name('addlease.store');
    Route::get('propertyaddlease/show/{Id}', [PropertyNewLeaseController::class, 'show'])->name('addlease.show');
    Route::get('propertyaddlease/edit/{Id}', [PropertyNewLeaseController::class, 'edit'])->name('addlease.edit');
    Route::put('propertyaddlease/edit/{Id}', [PropertyNewLeaseController::class, 'update'])->name('addlease.update');
    Route::delete('propertyaddlease/delete/{Id}', [PropertyNewLeaseController::class, 'destroy'])->name('addlease.destroy');
    Route::get('/propertyaddlease/blocks/{PropertyId}', [PropertyNewLeaseController::class, 'getBlockByProperty'])->name('getblockbyproperty');
    Route::get('/propertyaddlease/floors/{BlockId}', [PropertyNewLeaseController::class, 'getFloorByBlock'])->name('getfloorbyblock');
    Route::get('/propertyaddlease/Units/{FloorId}', [PropertyNewLeaseController::class, 'getUnitByFloor'])->name('getunitbyfloor');


    //Route::resource('terminatelease', PropertyLeaseTerminationController::class);
    Route::get('propertyterminatelease', [PropertyLeaseTerminationController::class, 'index'])->name('terminatelease.index');
    Route::get('propertyterminatelease/create', [PropertyLeaseTerminationController::class, 'create'])->name('terminatelease.create');
    Route::post('propertyterminatelease', [PropertyLeaseTerminationController::class, 'store'])->name('terminatelease.store');
    Route::get('propertyterminatelease/show/{Id}', [PropertyLeaseTerminationController::class, 'show'])->name('terminatelease.show');
    Route::get('propertyterminatelease/edit/{Id}', [PropertyLeaseTerminationController::class, 'edit'])->name('terminatelease.edit');
    Route::put('propertyterminatelease/edit/{Id}', [PropertyLeaseTerminationController::class, 'update'])->name('terminatelease.update');


    //Route::resource('schedulelease', PropertyLeaseScheduleController::class);
    Route::get('schedulelease', [PropertyLeaseScheduleController::class, 'index'])->name('schedulelease.index');
    Route::get('schedulelease/create', [PropertyLeaseScheduleController::class, 'create'])->name('schedulelease.create');
    Route::post('schedulelease', [PropertyLeaseScheduleController::class, 'store'])->name('schedulelease.store');
    Route::get('schedulelease/property/{Id}', [PropertyLeaseScheduleController::class, 'getPropertyByTenant'])->name('getpropertybytenant');
    Route::get('schedulelease/lease/{Id}', [PropertyLeaseScheduleController::class, 'getLeaseByProperty'])->name('getleasebyproperty');
    Route::get('schedulelease/show/{id}', [PropertyLeaseScheduleController::class, 'show'])->name('schedulelease.show');
    Route::get('schedulelease/edit/{Id}', [PropertyLeaseScheduleController::class, 'edit'])->name('schedulelease.edit');
    Route::put('schedulelease/edit/{Id}', [PropertyLeaseScheduleController::class, 'update'])->name('schedulelease.update');
    Route::delete('schedulelease/delete/{Id}', [PropertyLeaseScheduleController::class, 'destroy'])->name('schedulelease.destroy');
    Route::get('/schedulelease/print/{Id}', [PropertyLeaseScheduleController::class, 'print'])->name('schedulelease.print');


    //Route::resource('renewlease', PropertyLeaseRenewalController::class);
    Route::get('renewlease', [PropertyLeaseRenewalController::class, 'index'])->name('renewlease.index');
    Route::get('renewlease/create', [PropertyLeaseRenewalController::class, 'create'])->name('renewlease.create');
    Route::post('renewlease', [PropertyLeaseRenewalController::class, 'store'])->name('renewlease.store');
    Route::get('renewlease/property/{Id}', [PropertyLeaseRenewalController::class, 'getPropertyByTenant'])->name('getpropertybytenant');
    Route::get('renewlease/lease/{Id}', [PropertyLeaseRenewalController::class, 'getLeaseByProperty'])->name('getleasebyproperty');
    Route::get('renewlease/show/{id}', [PropertyLeaseRenewalController::class, 'show'])->name('renewlease.show');
    Route::get('renewlease/edit/{Id}', [PropertyLeaseRenewalController::class, 'edit'])->name('renewlease.edit');
    Route::put('renewlease/edit/{Id}', [PropertyLeaseRenewalController::class, 'update'])->name('renewlease.update');
    Route::delete('renewlease/delete/{Id}', [PropertyLeaseRenewalController::class, 'destroy'])->name('renewlease.destroy');

    //Route::resource('rentinvoice', PropertyInvoiceController::class);
    Route::get('rentinvoice', [PropertyInvoiceController::class,'index'])->name('rentinvoice.index');
    Route::get('rentinvoice/create', [PropertyInvoiceController::class,'create'])->name('rentinvoice.create');
    Route::post('rentinvoice', [PropertyInvoiceController::class,'store'])->name('rentinvoice.store');
    Route::get('rentinvoice/show/{id}', [PropertyInvoiceController::class,'show'])->name('rentinvoice.show');
    Route::get('rentinvoice/edit/{id}',[PropertyInvoiceController::class,'edit'])->name('rentinvoice.edit');
    Route::put('rentinvoice/edit/{id}',[PropertyInvoiceController::class,'update'])->name('rentinvoice.update');
    Route::delete('rentinvoice/delete/{id}', [PropertyInvoiceController::class,'destroy'])->name('rentinvoice.destroy');

    //Route::resource('rentreceipt', PropertyReceiptController::class);
    Route::get('rentreceipt', [PropertyReceiptController::class,'index'])->name('rentreceipt.index');
    Route::get('/rentreceipt/amount-paid/{invoiceId}', [PropertyReceiptController::class, 'getAmountPaidSoFar'])->name('rentreceipt.amountPaid');
    Route::get('rentreceipt/create', [PropertyReceiptController::class,'create'])->name('rentreceipt.create');
    Route::post('rentreceipt', [PropertyReceiptController::class,'store'])->name('rentreceipt.store');
    Route::get('rentreceipt/show/{Id}', [PropertyReceiptController::class,'show'])->name('rentreceipt.show');
    Route::get('rentreceipt/edit/{Id}',[PropertyReceiptController::class,'edit'])->name('rentreceipt.edit');
    Route::put('rentreceipt/edit/{Id}',[PropertyReceiptController::class,'update'])->name('rentreceipt.update');
    Route::delete('rentreceipt/delete/{Id}', [PropertyReceiptController::class,'destroy'])->name('rentreceipt.destroy');
    Route::get('/rentreceipt/print/{Id}', [PropertyReceiptController::class, 'print'])->name('rentreceipt.pdf');



    //Route::resource('tenantledger', TenantStatementController::class);
    Route::get('tenantledger', [TenantStatementController::class, 'index'])->name('tenantledger.index');
    // Route::get('tenantledger/create', [TenantStatementController::class,'create'])->name('tenantledger.create');
    // Route::get('tenantledger/store', [TenantStatementController::class,'store'])->name('tenantledger.store');   
     Route::get('tenantledger/pdf', [TenantStatementController::class,'exportpdf'])->name('tenantledger.pdf');
    
    //Route::resource('maintenancerequest', PropertyMaintenanceRequestController::class);
    Route::get('maintenancerequest', [PropertyMaintenanceRequestController::class,'index'])->name('maintenancerequest.index');
    Route::get('maintenancerequest/create', [PropertyMaintenanceRequestController::class,'create'])->name('maintenancerequest.create');
    Route::post('maintenancerequest', [PropertyMaintenanceRequestController::class,'store'])->name('maintenancerequest.store');
    Route::get('maintenancerequest/show/{Id}', [PropertyMaintenanceRequestController::class,'show'])->name('maintenancerequest.show');
    Route::get('maintenancerequest/edit/{Id}',[PropertyMaintenanceRequestController::class,'edit'])->name('maintenancerequest.edit');
    Route::put('maintenancerequest/edit/{Id}',[PropertyMaintenanceRequestController::class,'update'])->name('maintenancerequest.update');
    Route::delete('maintenancerequest/delete/{Id}', [PropertyMaintenanceRequestController::class,'destroy'])->name('maintenancerequest.destroy');

    //Route::resource('assignrequest', PropertyMaintananceAssignController::class);
    Route::get('assignrequest', [PropertyMaintananceAssignController::class,'index'])->name('assignrequest.index');
    Route::get('assignrequest/create', [PropertyMaintananceAssignController::class,'create'])->name('assignrequest.create');
    Route::post('assignrequest', [PropertyMaintananceAssignController::class,'store'])->name('assignrequest.store');
    Route::get('assignrequest/show/{Id}', [PropertyMaintananceAssignController::class,'show'])->name('assignrequest.show');
    Route::get('assignrequest/edit/{Id}',[PropertyMaintananceAssignController::class,'edit'])->name('assignrequest.edit');
    Route::put('assignrequest/edit/{Id}',[PropertyMaintananceAssignController::class,'update'])->name('assignrequest.update');
    Route::delete('assignrequest/delete/{Id}', [PropertyMaintananceAssignController::class,'destroy'])->name('assignrequest.destroy');

    
    //Route::resource('workcompletion', PropertyMaintenanceWorkCompletionController::class);
    Route::get('workcompletion', [PropertyMaintenanceWorkCompletionController::class,'index'])->name('workcompletion.index');
    Route::get('workcompletion/create', [PropertyMaintenanceWorkCompletionController::class,'create'])->name('workcompletion.create');
    Route::post('workcompletion', [PropertyMaintenanceWorkCompletionController::class,'store'])->name('workcompletion.store');
    Route::get('workcompletion/show/{Id}', [PropertyMaintenanceWorkCompletionController::class,'show'])->name('workcompletion.show');
    Route::get('workcompletion/edit/{Id}',[PropertyMaintenanceWorkCompletionController::class,'edit'])->name('workcompletion.edit');
    Route::put('workcompletion/edit/{Id}',[PropertyMaintenanceWorkCompletionController::class,'update'])->name('workcompletion.update');
    Route::delete('workcompletion/delete/{Id}', [PropertyMaintenanceWorkCompletionController::class,'destroy'])->name('workcompletion.destroy');

    
    Route::resource('rentdashboard', RentDashboardController::class);
    Route::resource('maintenancedashboard', PropertyMaintenanceDashboardController::class);
    Route::resource('propertyreports', PropertyReportsController::class);
    Route::resource('propertyanalytics', PropertyReportsVisualController::class);


    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('property-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'property-reports.index',
        'show' => 'property-reports.show'
    ]);
});
