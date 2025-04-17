<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ItemController;
use App\Http\Controllers\Procurement\ItemCategoryController;
use App\Http\Controllers\Procurement\ModeTimelineController;
use App\Http\Controllers\Procurement\ProcurementModeController;
use App\Http\Controllers\Procurement\TenderingProcessController;

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
});