<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Property\AddPropertyController;


Route::namespace('Property')->group(function () {
    Route::resource('addproperty', AddPropertyController::class);
    Route::resource('propertytype', AddPropertyController::class);
});