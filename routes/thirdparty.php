<?php

use App\Http\Controllers\Web\ThirdParty\ThirdPartyWebController;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Procurement\ThirdParty\SupplierCategoryController;

Route::prefix('thirdparty')->name('thirdparty.')->group(function () {
    Route::resource('parties', ThirdPartyWebController::class)
        ->only([
            'index',
            'show',
            'edit',
            'store',
            'create',
            'update',
            'destroy'
        ]);

    // Route::resource('supplier-categories', SupplierCategoryController::class);
});
