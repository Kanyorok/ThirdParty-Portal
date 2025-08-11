<?php

use App\Http\Controllers\FleetManagement\ComplianceAndDocumentationController;
use App\Http\Controllers\FleetManagement\DriverManagementController;
use App\Http\Controllers\FleetManagement\FleetMakeController;
use App\Http\Controllers\FleetManagement\FleetModelController;
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

   // Route::resource('drivermanagement', DriverManagementController::class);
    Route::get('/drivermanagement', [DriverManagementController::class, 'index'])->name('drivermanagement.index');
    Route::get('/drivermanagement/create', [DriverManagementController::class, 'create'])->name('drivermanagement.create');
    Route::get('/drivermanagement/{Id}', [DriverManagementController::class, 'show'])->name('drivermanagement.show');
    Route::get('/drivermanagement/{Id}/edit', [DriverManagementController::class, 'edit'])->name('drivermanagement.edit');
    Route::put('/drivermanagement/{Id}', [DriverManagementController::class, 'update'])->name('drivermanagement.update');
    Route::post('/drivermanagement', [DriverManagementController::class, 'store'])->name('drivermanagement.store');
    Route::delete('/drivermanagement/{Id}', [DriverManagementController::class, 'destroy'])->name('drivermanagement.destroy');

    // Route::resource('fleetmake', FleetMakeController::class);
    Route::get('/fleetmake', [FleetMakeController::class, 'index'])->name('fleetmake.index');
    Route::get('/fleetmake/create', [FleetMakeController::class, 'create'])->name('fleetmake.create');
    Route::get('/fleetmake/{Id}', [FleetMakeController::class, 'show'])->name('fleetmake.show');
    Route::get('/fleetmake/{Id}/edit', [FleetMakeController::class, 'edit'])->name('fleetmake.edit');
    Route::put('/fleetmake/{Id}', [FleetMakeController::class, 'update'])->name('fleetmake.update');
    Route::post('/fleetmake', [FleetMakeController::class, 'store'])->name('fleetmake.store');
    Route::delete('/fleetmake/{Id}', [FleetMakeController::class, 'destroy'])->name('fleetmake.destroy');

    // Route::resource('fleetmodel', FleetModelController::class);
    Route::get('/fleetmodel', [FleetModelController::class, 'index'])->name('fleetmodel.index');
    Route::get('/fleetmodel/create', [FleetModelController::class, 'create'])->name('fleetmodel.create');
    Route::get('/fleetmodel/{Id}', [FleetModelController::class, 'show'])->name('fleetmodel.show');
    Route::get('/fleetmodel/{Id}/edit', [FleetModelController::class, 'edit'])->name('fleetmodel.edit');
    Route::put('/fleetmodel/{Id}', [FleetModelController::class, 'update'])->name('fleetmodel.update');
    Route::post('/fleetmodel', [FleetModelController::class, 'store'])->name('fleetmodel.store');
    Route::delete('/fleetmodel/{Id}', [FleetModelController::class, 'destroy'])->name('fleetmodel.destroy');
    Route::resource('servicetracking', ServiceTrackingController::class);
    Route::resource('licensing', LicensingController::class);
 
    
    //Route::resource('vehicle-registry', VehicleManagementController::class);
    Route::get('/vehicle-registry', [VehicleManagementController::class, 'index'])->name('vehicle-registry.index');
    Route::get('/vehicle-registry/create', [VehicleManagementController::class, 'create'])->name('vehicle-registry.create');
    Route::get('/vehicle-registry/{Id}', [VehicleManagementController::class, 'show'])->name('vehicle-registry.show');
    Route::get('/vehicle-registry/{Id}/edit', [VehicleManagementController::class, 'edit'])->name('vehicle-registry.edit');
    Route::put('/vehicle-registry/{Id}', [VehicleManagementController::class, 'update'])->name('vehicle-registry.update');
    Route::post('/vehicle-registry', [VehicleManagementController::class, 'store'])->name('vehicle-registry.store');
    Route::delete('/vehicle-registry/{Id}', [VehicleManagementController::class, 'destroy'])->name('vehicle-registry.destroy');


    Route::resource('complianceanddocumentation', ComplianceAndDocumentationController::class);
    Route::resource('fleetprocurementanddisposal', FleetProcurementAndDisposalController::class);
    Route::resource('inventoryofspareparts', InventoryOfSparePartsController::class);
    Route::resource('utilization', UtilizationController::class);
    Route::resource('reports', ReportsController::class);
    Route::resource('gps', GpsController::class);
});
