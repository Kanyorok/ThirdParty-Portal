<?php

use App\Http\Controllers\Procurement\SupplierCategoryController;
use App\Http\Controllers\Procurement\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('proc')->name('proc.')->group(function () {
        Route::resource('supplier-cat', SupplierCategoryController::class)
            ->names('supplier-cat');
        Route::resource('supp', SupplierController::class)
            ->names('supp');
    });
});
