<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ItemController;
use App\Http\Controllers\Procurement\ItemCategoryController;

Route::namespace('Procurement')->group(function () {

    //Requisitions
    Route::resource('requisition', 'RequisitionsController');
    Route::resource('requisitionItem', 'RequisitionItemsController');
    Route::get('requisitionItem/getItem/{type}', 'RequisitionItemsController@getItems')->name('requisitionItem.getItems');
    Route::get('requisitionItem/getItemDetails/{item}', 'RequisitionItemsController@getItemDetails')->name('requisitionItem.getItemDetails');

//    Route::get('requisitionItem/getItem/{type}', [RequisitionItemsController::class, 'getItems'])->name('requisitionItem.getItems');


    //Items
    Route::resource('items', 'ItemController');
    Route::resource('categories', 'ItemCategoryController');
});
