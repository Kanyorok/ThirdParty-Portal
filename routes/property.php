<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Property\AddPropertyController;


Route::namespace('property')->group(function () {
    Route::resource('addproperty', AddPropertyController::class);
    Route::resource('propertytype', AddPropertyController::class);

});