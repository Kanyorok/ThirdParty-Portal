<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Property\AddPropertyController;
use App\Http\Controllers\Property\PropertyTypeController;

Route::namespace('Property')->group(function () {
    Route::resource('addproperty', AddPropertyController::class);
    Route::resource('propertytype', PropertyTypeController::class);
});