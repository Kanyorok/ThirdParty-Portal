<?php

use App\Http\Controllers\Procurement\RequisitionItemsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ItemController;
use App\Http\Controllers\Procurement\ModeTimelineController;
use App\Http\Controllers\Procurement\ProcurementModeController;
use App\Http\Controllers\Procurement\TenderController;
use App\Http\Controllers\Procurement\SasraAuditorController;
use App\Http\Controllers\Procurement\EngagedAuditorController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\ProcurementPeriodController;
use App\Http\Controllers\Procurement\ProcurementPlanController;
use App\Http\Controllers\Procurement\RFQController;
use App\Http\Controllers\Procurement\RFQResponseController;

Route::namespace('Procurement')->group(function () {

    //Requisitions
    Route::resource('requisition', 'RequisitionsController');
    Route::resource('requisitionItem', 'RequisitionItemsController');
    Route::get('items/download', [ItemController::class, 'download'])->name('items.download');
    Route::get('requisitionItem/getItem/{type}', 'RequisitionItemsController@getItems')->name('requisitionItem.getItems');
    Route::get('requisitionItem/getItemDetails/{item}', 'RequisitionItemsController@getItemDetails')->name('requisitionItem.getItemDetails');
//    Route::get('requisitionItem/{id}', 'RequisitionItemsController@getRelatedRequisitionLines')->name('requisitionItem.list');
    Route::get('requisition/{id}', [RequisitionItemsController::class, 'show'])->name('requisitionItem.show');
    Route::get('requisitionItem/create/{id}', [RequisitionItemsController::class, 'create'])->name('requisitionItem.create');



    //Route::get('requisitionItem/getItem/{type}', [RequisitionItemsController::class, 'getItems'])->name('requisitionItem.getItems');


    //Items
    Route::resource('items', 'ItemController');
    Route::resource('categories', 'ItemCategoryController');

    // Procurement Modes
    Route::resource('procurement-modes', ProcurementModeController::class);

    //Mode Timelines
    Route::post('/timelines', [ModeTimelineController::class, 'store'])->name('timelines.store');
    Route::delete('timelines/{id}', [ModeTimelineController::class, 'destroy'])->name('timelines.destroy');
    Route::get('timelines/{id}/edit', [ModeTimelineController::class, 'edit'])->name('timelines.edit');
    Route::put('timelines/{id}', [ModeTimelineController::class, 'update'])->name('timelines.update');

    // Tendering Process
    Route::resource('tendering-process', TenderController::class);

    //Auditor Routes
    Route::get('/sasra-auditors', [SasraAuditorController::class, 'index'])->name('sasra-auditors.index');
    Route::get('/sasra-auditors/upload', [SasraAuditorController::class, 'showImportForm'])->name('sasra-auditors.show');
    Route::post('/sasra-auditors/upload', [SasraAuditorController::class, 'import'])->name('sasra-auditors.import');
    Route::get('/sasra-auditors/download', [SasraAuditorController::class, 'download'])->name('sasra-auditors.download');
    Route::get('/engaged-auditors', [EngagedAuditorController::class, 'index'])->name('engaged-auditors.index');
    Route::post('/engaged-auditors', [EngagedAuditorController::class, 'store'])->name('engaged-auditors.store');
    Route::get('/engaged-auditors/create', [EngagedAuditorController::class, 'create'])->name('engaged-auditors.create');
    Route::get('/engaged-auditors/{id}/edit', [EngagedAuditorController::class, 'edit'])->name('engaged-auditors.edit');
    Route::put('/engaged-auditors/{id}', [EngagedAuditorController::class, 'update'])->name('engaged-auditors.update');
    Route::delete('/engaged-auditors/{id}', [EngagedAuditorController::class, 'destroy'])->name('engaged-auditors.destroy');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Procurement Periods
    Route::resource('procurement-periods', ProcurementPeriodController::class);

    Route::get('/procurement-periods/{id}/assign-suppliers', [ProcurementPeriodController::class, 'assignSuppliersForm'])->name('procurement-periods.assign-suppliers-form');
    Route::post('/procurement-periods/{id}/assign-suppliers', [ProcurementPeriodController::class, 'assignSuppliers'])->name('procurement-periods.assign-suppliers');

    Route::get('/procurement-periods/{period}/plans/create', [ProcurementPlanController::class, 'create'])->name('procurement-periods.plans.create');
    Route::post('/procurement-periods/{period}/plans', [ProcurementPlanController::class, 'store'])->name('procurement-periods.plans.store');

    // RFQ routes
    Route::get('/rfqs/create', [RFQController::class, 'create'])->name('rfqs.create');
    Route::post('/rfqs', [RFQController::class, 'store'])->name('rfqs.store');
    Route::get('/rfqs/{id}', [RFQController::class, 'show'])->name('rfqs.show');
    Route::get('/rfqs', [RFQController::class, 'index'])->name('rfqs.index');
    Route::post('/rfqs/{rfq}/approve', [RFQController::class, 'approve'])->name('rfqs.approve');
    Route::post('/rfqs/{rfq}/reject', [RFQController::class, 'reject'])->name('rfqs.reject');
    Route::get('/rfqs/{rfqId}/requisition-items', [RFQController::class, 'getRequisitionItems']);

    // RFQ Response routes
    Route::get('/rfqresponses', [RFQResponseController::class, 'index'])->name('rfqresponses.index');
    Route::get('/rfqresponses/create', [RFQResponseController::class, 'create'])->name('rfqresponses.create');
    Route::post('/rfqresponses', [RFQResponseController::class, 'store'])->name('rfqresponses.store');
    Route::get('/rfqresponses/{id}', [RFQResponseController::class, 'show'])->name('rfqresponses.show');
    Route::get('/rfqresponses/{id}/edit', [RFQResponseController::class, 'edit'])->name('rfqresponses.edit');
    Route::put('/rfqresponses/{id}', [RFQResponseController::class, 'update'])->name('rfqresponses.update');
    Route::delete('/rfqresponses/{id}', [RFQResponseController::class, 'destroy'])->name('rfqresponses.destroy');
});
