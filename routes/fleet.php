<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Fleetmanagement\TripManagementController;
use App\Http\Controllers\Fleetmanagement\FuelManagementController;


Route::namespace('Fleetmanagement')->group(function () {
    Route::resource('tripmanagement', TripManagementController::class);
    Route::resource('fuelmanagement', FuelManagementController::class);


});
