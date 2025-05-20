<?php

use App\Http\Controllers\FleetManagement\ComplianceAndDocumentationController;
use App\Http\Controllers\FleetManagement\DriverManagementController;
use App\Http\Controllers\FleetManagement\FleetProcurementAndDisposalController;
use App\Http\Controllers\FleetManagement\FuelManagementController;
use App\Http\Controllers\FleetManagement\GpsController;
use App\Http\Controllers\FleetManagement\InventoryOfSparePartsController;
use App\Http\Controllers\FleetManagement\LicensingController;
use App\Http\Controllers\FleetManagement\ReportsController;
use App\Http\Controllers\FleetManagement\ServiceTrackingController;
use App\Http\Controllers\FleetManagement\TripManagementController;
use App\Http\Controllers\FleetManagement\UtilizationController;
use App\Http\Controllers\FleetManagement\VehicleManagementController;
use Illuminate\Support\Facades\Route;


Route::namespace('FleetManagement')->prefix('fleet')->group(function () {
    Route::resource('tripmanagement', TripManagementController::class);
    Route::resource('fuelmanagement', FuelManagementController::class);
    Route::resource('drivermanagement', DriverManagementController::class);
    Route::resource('servicetracking', ServiceTrackingController::class);
    Route::resource('licensing', LicensingController::class);
    Route::resource('vehicle-registry', VehicleManagementController::class);
    Route::resource('complianceanddocumentation', ComplianceAndDocumentationController::class);
    Route::resource('fleetprocurementanddisposal', FleetProcurementAndDisposalController::class);
    Route::resource('inventoryofspareparts', InventoryOfSparePartsController::class);
    Route::resource('utilization', UtilizationController::class);
    Route::resource('reports', ReportsController::class);
    Route::resource('gps', GpsController::class);
});
