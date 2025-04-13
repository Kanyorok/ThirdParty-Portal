<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Procurement')->group(function () {

    //Requisitions
    Route::resource('requisition', 'Requisitions');
    Route::resource('requisitionItem', 'RequisitionItems');


});
