<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ItemController;
use App\Http\Controllers\Procurement\ModeTimelineController;
use App\Http\Controllers\Procurement\ProcurementModeController;
use App\Http\Controllers\Procurement\TenderController;
use App\Http\Controllers\Procurement\SasraAuditorController;
use App\Http\Controllers\Procurement\EngagedAuditorController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\ProcurementPeriodController;

Route::namespace('Procurement')->group(function () {

    //Requisitions
    Route::resource('requisition', 'RequisitionsController');
    Route::resource('requisitionItem', 'RequisitionItemsController');
    Route::get('items/download', [ItemController::class, 'download'])->name('items.download');
    Route::get('requisitionItem/getItem/{type}', 'RequisitionItemsController@getItems')->name('requisitionItem.getItems');
    Route::get('requisitionItem/getItemDetails/{item}', 'RequisitionItemsController@getItemDetails')->name('requisitionItem.getItemDetails');

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
    Route::resource('procurement-periods', ProcurementPeriodController::class)->except(['show']);
});