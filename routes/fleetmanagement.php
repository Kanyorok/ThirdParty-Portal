<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Fleetmanagement\DriverManagementController;


Route::namespace('Fleetmanagement')->group(function () {
    Route::resource('drivermanagement', DriverManagementController::class);
});
