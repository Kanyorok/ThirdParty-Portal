<?php

use App\Http\Controllers\FleetManagement\ComplianceAndDocumentationController;
use App\Http\Controllers\FleetManagement\DriverManagementController;
use App\Http\Controllers\FleetManagement\FleetProcurementAndDisposalController;
use App\Http\Controllers\FleetManagement\FuelManagementController;
use App\Http\Controllers\FleetManagement\InventoryOfSparePartsController;
use App\Http\Controllers\FleetManagement\LicensingController;
use App\Http\Controllers\FleetManagement\ReportsController;
use App\Http\Controllers\FleetManagement\ServiceTrackingController;
use App\Http\Controllers\FleetManagement\TripManagementController;
use App\Http\Controllers\FleetManagement\UtilizationController;
use App\Http\Controllers\FleetManagement\VehicleManagementController;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Fleet\VehicleController;
use App\Http\Controllers\Fleet\FleetVehicleAssignmentController;
use App\Http\Controllers\Fleet\VehicleDocumentController;
use App\Http\Controllers\Fleet\FleetDriverController;
use App\Http\Controllers\Fleet\FleetDriverAssignmentController;
use App\Http\Controllers\Fleet\FleetDriverLicenseTrackingController;
use App\Http\Controllers\Fleet\ContractedDriverController;
use App\Http\Controllers\Fleet\FleetContractedDriverAssignmentController;
use App\Http\Controllers\Fleet\FleetContractedDriverLicenseTrackingController;
use App\Http\Controllers\Fleet\FleetTripLogController;
use App\Http\Controllers\Fleet\FleetRoutePlannerController;
use App\Http\Controllers\Fleet\FleetFuelLogController;
use App\Http\Controllers\Fleet\FleetVehicleRequestController;
use App\Http\Controllers\Fleet\FleetMaintenanceScheduleController;
use App\Http\Controllers\Fleet\FleetRepairLogController;
use App\Http\Controllers\Fleet\FleetServiceAlertController;
use App\Http\Controllers\Fleet\FleetAlertRuleController;
use App\Http\Controllers\Fleet\FleetRunningCostController;
use App\Http\Controllers\Fleet\FleetGpsController;
use App\Http\Controllers\Fleet\FleetTelematicsDeviceController;
use App\Http\Controllers\Fleet\FleetInsuranceTrackerController;
use App\Http\Controllers\Fleet\FleetInspectionScheduleController;


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
    

});
Route::prefix('fleet/vehicles')->name('fleet.vehicles.')->middleware(['auth'])->group(function () {
    Route::get('/', [VehicleController::class, 'index'])->name('index');              // List all vehicles
    Route::get('/create', [VehicleController::class, 'create'])->name('create');      // Show registration form
    Route::post('/store', [VehicleController::class, 'store'])->name('store');        // Save new vehicle
    Route::get('/edit/{id}', [VehicleController::class, 'edit'])->name('edit');       // Show edit form
    Route::put('/update/{id}', [VehicleController::class, 'update'])->name('update'); // Update vehicle
    Route::delete('/{id}/deactivate', [VehicleController::class, 'deactivate'])->name('deactivate'); // Soft delete


});

Route::prefix('fleet/assignments')->name('fleet.assignments.')->middleware('auth')->group(function () {
    Route::get('/', [FleetVehicleAssignmentController::class, 'index'])->name('index');
    Route::get('/create', [FleetVehicleAssignmentController::class, 'create'])->name('create');
    Route::post('/store', [FleetVehicleAssignmentController::class, 'store'])->name('store');
});

Route::prefix('fleet/documents')->name('fleet.documents.')->middleware(['auth'])->group(function () {
    Route::get('/', [VehicleDocumentController::class, 'index'])->name('index');
    Route::get('/create', [VehicleDocumentController::class, 'create'])->name('create');
    Route::post('/store', [VehicleDocumentController::class, 'store'])->name('store');
});


Route::prefix('fleet')->name('fleet.')->group(function () {
    // Driver CRUD
    Route::get('drivers', [FleetDriverController::class, 'index'])->name('drivers.index');
    Route::get('drivers/create', [FleetDriverController::class, 'create'])->name('drivers.create');
    Route::post('drivers', [FleetDriverController::class, 'store'])->name('drivers.store');
    Route::get('drivers/{id}/edit', [FleetDriverController::class, 'edit'])->name('drivers.edit');
    Route::get('drivers/{id}', [FleetDriverController::class, 'show'])->name('drivers.show');
    Route::put('drivers/{id}', [FleetDriverController::class, 'update'])->name('drivers.update');
    Route::put('drivers/{id}/deactivate', [FleetDriverController::class, 'deactivate'])->name('drivers.deactivate');
});

Route::prefix('fleet')->name('fleet.')->group(function () {
    Route::get('driver-assignments', [FleetDriverAssignmentController::class, 'index'])->name('driver_assignments.index');
    Route::get('driver-assignments/create', [FleetDriverAssignmentController::class, 'create'])->name('driver_assignments.create');
    Route::post('driver-assignments/store', [FleetDriverAssignmentController::class, 'store'])->name('driver_assignments.store');
});

Route::prefix('fleet')->name('fleet.')->group(function () {
    // License tracking
    Route::get('drivers/{driver}/licenses', [FleetDriverLicenseTrackingController::class, 'index'])->name('licenses.index');
    Route::get('drivers/{driver}/licenses/create', [FleetDriverLicenseTrackingController::class, 'create'])->name('licenses.create');
    Route::post('licenses/store', [FleetDriverLicenseTrackingController::class, 'store'])->name('licenses.store');
});



Route::prefix('fleet')->name('fleet.')->group(function () {
    // Contracted Drivers
    Route::get('contracted-drivers', [ContractedDriverController::class, 'index'])->name('contracted_drivers.index');
    Route::get('contracted-drivers/create', [ContractedDriverController::class, 'create'])->name('contracted_drivers.create');
    Route::post('contracted-drivers/store', [ContractedDriverController::class, 'store'])->name('contracted_drivers.store');
    Route::get('contracted-drivers/{id}/edit', [ContractedDriverController::class, 'edit'])->name('contracted_drivers.edit');
    Route::get('contracted-drivers/{id}', [ContractedDriverController::class, 'show'])->name('contracted_drivers.show');
    Route::put('contracted-drivers/{id}/update', [ContractedDriverController::class, 'update'])->name('contracted_drivers.update');
    Route::put('contracted-drivers/{id}/deactivate', [ContractedDriverController::class, 'deactivate'])->name('contracted_drivers.deactivate');
});


Route::prefix('fleet/contracted-drivers/{driverId}/licenses')->name('fleet.contracted_driver_licenses.')->group(function () {
    Route::get('/', [FleetContractedDriverLicenseTrackingController::class, 'index'])->name('index');
    Route::get('create', [FleetContractedDriverLicenseTrackingController::class, 'create'])->name('create');
    Route::post('/', [FleetContractedDriverLicenseTrackingController::class, 'store'])->name('store');
});

Route::prefix('fleet/contracted-drivers/{driverId}/assignments')
    ->name('fleet.contracted_driver_assignments.')
    ->group(function () {
        Route::get('/', [FleetContractedDriverAssignmentController::class, 'index'])->name('index');
        Route::get('create', [FleetContractedDriverAssignmentController::class, 'create'])->name('create');
        Route::post('/', [FleetContractedDriverAssignmentController::class, 'store'])->name('store');
    });

    Route::prefix('fleet')->name('fleet.')->group(function () {
    Route::get('trip-logs', [FleetTripLogController::class, 'index'])->name('trip_logs.index');
    Route::get('trip-logs/create', [FleetTripLogController::class, 'create'])->name('trip_logs.create');
    Route::post('trip-logs/store', [FleetTripLogController::class, 'store'])->name('trip_logs.store');
});

Route::prefix('fleet')->name('fleet.')->group(function () {
    Route::get('route-planner', [FleetRoutePlannerController::class, 'index'])->name('route_planner.index');
    Route::get('route-planner/create', [FleetRoutePlannerController::class, 'create'])->name('route_planner.create');
    Route::post('route-planner/store', [FleetRoutePlannerController::class, 'store'])->name('route_planner.store');
});

Route::prefix('fleet')->name('fleet.')->group(function () {
    Route::prefix('fuel-logs')->name('fuel_logs.')->group(function () {
        Route::get('/', [FleetFuelLogController::class, 'index'])->name('index');
        Route::get('/create', [FleetFuelLogController::class, 'create'])->name('create');
        Route::post('/store', [FleetFuelLogController::class, 'store'])->name('store');
    });
});

Route::prefix('fleet')->name('fleet.')->group(function () {
    Route::prefix('vehicle-requests')->name('vehicle_requests.')->group(function () {
        Route::get('/', [FleetVehicleRequestController::class, 'index'])->name('index');
        Route::get('/create', [FleetVehicleRequestController::class, 'create'])->name('create');
        Route::post('/store', [FleetVehicleRequestController::class, 'store'])->name('store');

        Route::get('/{id}/approve', [FleetVehicleRequestController::class, 'approveForm'])->name('approve.form');
        Route::post('/{id}/approve', [FleetVehicleRequestController::class, 'approve'])->name('approve');
        Route::put('/{id}/cancel', [FleetVehicleRequestController::class, 'cancel'])->name('cancel');
    });
});

Route::prefix('fleet')->name('fleet.')->group(function () {
    // Maintenance Schedule
    Route::prefix('maintenance-schedule')->name('maintenance_schedule.')->group(function () {
        Route::get('/', [FleetMaintenanceScheduleController::class, 'index'])->name('index');
        Route::get('/create', [FleetMaintenanceScheduleController::class, 'create'])->name('create');
        Route::post('/store', [FleetMaintenanceScheduleController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [FleetMaintenanceScheduleController::class, 'edit'])->name('edit');
        Route::put('/{id}/update', [FleetMaintenanceScheduleController::class, 'update'])->name('update');
        Route::put('/{id}/cancel', [FleetMaintenanceScheduleController::class, 'cancel'])->name('cancel');
    });
});

Route::prefix('fleet/repair-logs')->name('fleet.repair_logs.')->group(function () {
    Route::get('/', [FleetRepairLogController::class, 'index'])->name('index');
    Route::get('create', [FleetRepairLogController::class, 'create'])->name('create');
    Route::post('store', [FleetRepairLogController::class, 'store'])->name('store');
    Route::get('{id}/edit', [FleetRepairLogController::class, 'edit'])->name('edit');
    Route::put('{id}/update', [FleetRepairLogController::class, 'update'])->name('update');
    Route::get('{id}/show', [FleetRepairLogController::class, 'show'])->name('show');
});

Route::prefix('fleet/service-alerts')->name('fleet.alerts.')->group(function () {
    Route::get('/', [FleetServiceAlertController::class, 'index'])->name('index');
    Route::put('{id}/acknowledge', [FleetServiceAlertController::class, 'acknowledge'])->name('acknowledge');
});

Route::prefix('fleet/alert-rules')->name('fleet.alert_rules.')->group(function () {
    Route::get('/', [FleetAlertRuleController::class, 'index'])->name('index');
    Route::get('create', [FleetAlertRuleController::class, 'create'])->name('create');
    Route::post('store', [FleetAlertRuleController::class, 'store'])->name('store');
    Route::get('{id}/edit', [FleetAlertRuleController::class, 'edit'])->name('edit');
    Route::put('{id}/update', [FleetAlertRuleController::class, 'update'])->name('update');
    Route::put('{id}/toggle', [FleetAlertRuleController::class, 'toggle'])->name('toggle');
});

Route::prefix('fleet/running-costs')->name('fleet.running_costs.')->group(function () {
    Route::get('/', [FleetRunningCostController::class, 'index'])->name('index');
    Route::get('/create', [FleetRunningCostController::class, 'create'])->name('create');
    Route::post('/store', [FleetRunningCostController::class, 'store'])->name('store');
});


Route::prefix('fleet/gps')->name('fleet.gps.')->middleware(['auth'])->group(function () {
    Route::get('live', [FleetGpsController::class, 'liveDashboard'])->name('live_dashboard');
    Route::get('movement-history', [FleetGpsController::class, 'movementHistory'])
        ->name('movement_history');
});


Route::prefix('fleet/telematics')->name('fleet.telematics.')->middleware(['auth'])->group(function () {
    Route::get('/', [FleetTelematicsDeviceController::class, 'index'])->name('index');          // List all devices
    Route::get('/create', [FleetTelematicsDeviceController::class, 'create'])->name('create');  // Show add form
    Route::post('/store', [FleetTelematicsDeviceController::class, 'store'])->name('store');    // Save new device
});


Route::prefix('fleet/insurance-tracker')->name('fleet.insurance_tracker.')->middleware(['auth'])->group(function () {
    Route::get('/', [FleetInsuranceTrackerController::class, 'index'])->name('index');
    Route::get('/create', [FleetInsuranceTrackerController::class, 'create'])->name('create');
    Route::post('/store', [FleetInsuranceTrackerController::class, 'store'])->name('store');
});


Route::prefix('fleet/compliance')->name('fleet.inspection_schedule.')->group(function () {
    Route::get('inspection-schedule', [FleetInspectionScheduleController::class, 'index'])->name('index');
    Route::get('inspection-schedule/create', [FleetInspectionScheduleController::class, 'create'])->name('create');
    Route::post('inspection-schedule', [FleetInspectionScheduleController::class, 'store'])->name('store');
});