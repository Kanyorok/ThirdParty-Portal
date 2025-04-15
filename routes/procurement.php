<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ItemController;
use App\Http\Controllers\Procurement\ItemCategoryController;

Route::namespace('Procurement')->group(function () {

    //Requisitions
    Route::resource('requisition', 'RequisitionsController');
    Route::resource('requisitionItem', 'RequisitionItemsController');
    Route::get('items/download', [ItemController::class, 'download'])->name('items.download');
    Route::resource('items', 'ItemController');
    Route::resource('categories', 'ItemCategoryController');
});
