<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Fleetmanagement\TripManagementController;
use App\Http\Controllers\Fleetmanagement\FuelManagementController;
use App\Http\Controllers\Fleetmanagement\DriverManagementController;
use App\Http\Controllers\Fleetmanagement\ServiceTrackingController;
use App\Http\Controllers\Fleetmanagement\LicensingController;
use App\Http\Controllers\Fleetmanagement\VehicleManagementController;
use App\Http\Controllers\Fleetmanagement\ComplianceAndDocumentationController;
use App\Http\Controllers\Fleetmanagement\FleetProcurementAndDisposalController;
use App\Http\Controllers\Fleetmanagement\InventoryOfSparePartsController;

Route::namespace('Fleetmanagement')->group(function () {
    Route::resource('tripmanagement', TripManagementController::class);
    Route::resource('fuelmanagement', FuelManagementController::class);
    Route::resource('drivermanagement', DriverManagementController::class);
    Route::resource('servicetracking', ServiceTrackingController::class);
    Route::resource('licensing', LicensingController::class);
    Route::resource('vehicle-registry', VehicleManagementController::class);
    Route::resource('complianceanddocumentation', ComplianceAndDocumentationController::class);
    Route::resource('fleetprocurementanddisposal', FleetProcurementAndDisposalController::class);
    Route::resource('inventoryofspareparts', InventoryOfSparePartsController::class);
    
});
