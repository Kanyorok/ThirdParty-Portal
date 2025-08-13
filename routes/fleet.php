<?php

use App\Http\Controllers\FleetManagement\ComplianceAndDocumentationController;
use App\Http\Controllers\FleetManagement\DriverManagementController;
use App\Http\Controllers\FleetManagement\FleetMakeController;
use App\Http\Controllers\FleetManagement\FleetModelController;
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
use App\Http\Controllers\FleetManagement\FuelTypeController;



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


    //Route::resource('fueltypes', FuelTypeController::class);

    Route::get('/fueltypes', [FuelTypeController::class, 'index'])->name('fueltypes.index');
    Route::get('/fueltypes/create', [FuelTypeController::class, 'create'])->name('fueltypes.create');
    Route::post('/fueltypes', [FuelTypeController::class, 'store'])->name('fueltypes.store');
    Route::get('/fueltypes/{Id}', [FuelTypeController::class, 'show'])->name('fueltypes.show');
    Route::get('/fueltypes/{Id}/edit', [FuelTypeController::class, 'edit'])->name('fueltypes.edit');
    Route::put('/fueltypes/{Id}', [FuelTypeController::class, 'update'])->name('fueltypes.update');
    Route::delete('/fueltypes/{Id}', [FuelTypeController::class, 'destroy'])->name('fueltypes.destroy');


    Route::resource('complianceanddocumentation', ComplianceAndDocumentationController::class);
    Route::resource('fleetprocurementanddisposal', FleetProcurementAndDisposalController::class);
    Route::resource('inventoryofspareparts', InventoryOfSparePartsController::class);
    Route::resource('utilization', UtilizationController::class);
    Route::resource('reports', ReportsController::class);
    

        });
        // ==================== Fleet Vehicles ====================
        Route::get('/fleet/vehicles', [VehicleController::class, 'index'])->name('fleet.vehicles.index')->middleware('auth');
        Route::get('/fleet/vehicles/create', [VehicleController::class, 'create'])->name('fleet.vehicles.create')->middleware('auth');
        Route::post('/fleet/vehicles', [VehicleController::class, 'store'])->name('fleet.vehicles.store')->middleware('auth');
        Route::get('/fleet/vehicles/{Id}/edit', [VehicleController::class, 'edit'])->name('fleet.vehicles.edit')->middleware('auth');
        Route::put('/fleet/vehicles/{Id}', [VehicleController::class, 'update'])->name('fleet.vehicles.update')->middleware('auth');
        Route::delete('/fleet/vehicles/{Id}/deactivate', [VehicleController::class, 'deactivate'])->name('fleet.vehicles.deactivate')->middleware('auth');

        // ==================== Fleet Vehicle Assignments ====================
        Route::get('/fleet/assignments', [FleetVehicleAssignmentController::class, 'index'])->name('fleet.assignments.index')->middleware('auth');
        Route::get('/fleet/assignments/create', [FleetVehicleAssignmentController::class, 'create'])->name('fleet.assignments.create')->middleware('auth');
        Route::post('/fleet/assignments', [FleetVehicleAssignmentController::class, 'store'])->name('fleet.assignments.store')->middleware('auth');

        // ==================== Fleet Vehicle Documents ====================
        Route::get('/fleet/documents', [VehicleDocumentController::class, 'index'])->name('fleet.documents.index')->middleware('auth');
        Route::get('/fleet/documents/create', [VehicleDocumentController::class, 'create'])->name('fleet.documents.create')->middleware('auth');
        Route::post('/fleet/documents', [VehicleDocumentController::class, 'store'])->name('fleet.documents.store')->middleware('auth');

        // ==================== Fleet Drivers ====================
        Route::get('/fleet/drivers', [FleetDriverController::class, 'index'])->name('fleet.drivers.index');
        Route::get('/fleet/drivers/create', [FleetDriverController::class, 'create'])->name('fleet.drivers.create');
        Route::post('/fleet/drivers', [FleetDriverController::class, 'store'])->name('fleet.drivers.store');
        Route::get('/fleet/drivers/{Id}/edit', [FleetDriverController::class, 'edit'])->name('fleet.drivers.edit');
        Route::get('/fleet/drivers/{Id}', [FleetDriverController::class, 'show'])->name('fleet.drivers.show');
        Route::put('/fleet/drivers/{Id}', [FleetDriverController::class, 'update'])->name('fleet.drivers.update');
        Route::put('/fleet/drivers/{Id}/deactivate', [FleetDriverController::class, 'deactivate'])->name('fleet.drivers.deactivate');

        // ==================== Fleet Driver Assignments ====================
        Route::get('/fleet/driver-assignments', [FleetDriverAssignmentController::class, 'index'])->name('fleet.driver_assignments.index');
        Route::get('/fleet/driver-assignments/create', [FleetDriverAssignmentController::class, 'create'])->name('fleet.driver_assignments.create');
        Route::post('/fleet/driver-assignments', [FleetDriverAssignmentController::class, 'store'])->name('fleet.driver_assignments.store');

        // ==================== Fleet Driver License Tracking ====================
        Route::get('/fleet/drivers/{driver}/licenses', [FleetDriverLicenseTrackingController::class, 'index'])->name('fleet.licenses.index');
        Route::get('/fleet/drivers/{driver}/licenses/create', [FleetDriverLicenseTrackingController::class, 'create'])->name('fleet.licenses.create');
        Route::post('/fleet/licenses', [FleetDriverLicenseTrackingController::class, 'store'])->name('fleet.licenses.store');

        // ==================== Contracted Drivers ====================
        Route::get('/fleet/contracted-drivers', [ContractedDriverController::class, 'index'])->name('fleet.contracted_drivers.index');
        Route::get('/fleet/contracted-drivers/create', [ContractedDriverController::class, 'create'])->name('fleet.contracted_drivers.create');
        Route::post('/fleet/contracted-drivers', [ContractedDriverController::class, 'store'])->name('fleet.contracted_drivers.store');
        Route::get('/fleet/contracted-drivers/{Id}/edit', [ContractedDriverController::class, 'edit'])->name('fleet.contracted_drivers.edit');
        Route::get('/fleet/contracted-drivers/{Id}', [ContractedDriverController::class, 'show'])->name('fleet.contracted_drivers.show');
        Route::put('/fleet/contracted-drivers/{Id}', [ContractedDriverController::class, 'update'])->name('fleet.contracted_drivers.update');
        Route::put('/fleet/contracted-drivers/{Id}/deactivate', [ContractedDriverController::class, 'deactivate'])->name('fleet.contracted_drivers.deactivate');

        // ==================== Contracted Driver Licenses ====================
        Route::get('/fleet/contracted-drivers/{driverId}/licenses', [FleetContractedDriverLicenseTrackingController::class, 'index'])->name('fleet.contracted_driver_licenses.index');
        Route::get('/fleet/contracted-drivers/{driverId}/licenses/create', [FleetContractedDriverLicenseTrackingController::class, 'create'])->name('fleet.contracted_driver_licenses.create');
        Route::post('/fleet/contracted-drivers/{driverId}/licenses', [FleetContractedDriverLicenseTrackingController::class, 'store'])->name('fleet.contracted_driver_licenses.store');

        // ==================== Contracted Driver Assignments ====================
        Route::get('/fleet/contracted-drivers/{driverId}/assignments', [FleetContractedDriverAssignmentController::class, 'index'])->name('fleet.contracted_driver_assignments.index');
        Route::get('/fleet/contracted-drivers/{driverId}/assignments/create', [FleetContractedDriverAssignmentController::class, 'create'])->name('fleet.contracted_driver_assignments.create');
        Route::post('/fleet/contracted-drivers/{driverId}/assignments', [FleetContractedDriverAssignmentController::class, 'store'])->name('fleet.contracted_driver_assignments.store');

        // ==================== Trip Logs ====================
        Route::get('/fleet/trip-logs', [FleetTripLogController::class, 'index'])->name('fleet.trip_logs.index');
        Route::get('/fleet/trip-logs/create', [FleetTripLogController::class, 'create'])->name('fleet.trip_logs.create');
        Route::post('/fleet/trip-logs', [FleetTripLogController::class, 'store'])->name('fleet.trip_logs.store');

        // ==================== Route Planner ====================
        Route::get('/fleet/route-planner', [FleetRoutePlannerController::class, 'index'])->name('fleet.route_planner.index');
        Route::get('/fleet/route-planner/create', [FleetRoutePlannerController::class, 'create'])->name('fleet.route_planner.create');
        Route::post('/fleet/route-planner', [FleetRoutePlannerController::class, 'store'])->name('fleet.route_planner.store');

        // ==================== Fuel Logs ====================
        Route::get('/fleet/fuel-logs', [FleetFuelLogController::class, 'index'])->name('fleet.fuel_logs.index');
        Route::get('/fleet/fuel-logs/create', [FleetFuelLogController::class, 'create'])->name('fleet.fuel_logs.create');
        Route::post('/fleet/fuel-logs', [FleetFuelLogController::class, 'store'])->name('fleet.fuel_logs.store');

        // ==================== Vehicle Requests ====================
        Route::get('/fleet/vehicle-requests', [FleetVehicleRequestController::class, 'index'])->name('fleet.vehicle_requests.index');
        Route::get('/fleet/vehicle-requests/create', [FleetVehicleRequestController::class, 'create'])->name('fleet.vehicle_requests.create');
        Route::post('/fleet/vehicle-requests', [FleetVehicleRequestController::class, 'store'])->name('fleet.vehicle_requests.store');
        Route::get('/fleet/vehicle-requests/{Id}/approve', [FleetVehicleRequestController::class, 'approveForm'])->name('fleet.vehicle_requests.approve.form');
        Route::post('/fleet/vehicle-requests/{Id}/approve', [FleetVehicleRequestController::class, 'approve'])->name('fleet.vehicle_requests.approve');
        Route::put('/fleet/vehicle-requests/{Id}/cancel', [FleetVehicleRequestController::class, 'cancel'])->name('fleet.vehicle_requests.cancel');

        // ==================== Maintenance Schedule ====================
        Route::get('/fleet/maintenance-schedule', [FleetMaintenanceScheduleController::class, 'index'])->name('fleet.maintenance_schedule.index');
        Route::get('/fleet/maintenance-schedule/create', [FleetMaintenanceScheduleController::class, 'create'])->name('fleet.maintenance_schedule.create');
        Route::post('/fleet/maintenance-schedule', [FleetMaintenanceScheduleController::class, 'store'])->name('fleet.maintenance_schedule.store');
        Route::get('/fleet/maintenance-schedule/{Id}/edit', [FleetMaintenanceScheduleController::class, 'edit'])->name('fleet.maintenance_schedule.edit');
        Route::put('/fleet/maintenance-schedule/{Id}', [FleetMaintenanceScheduleController::class, 'update'])->name('fleet.maintenance_schedule.update');
        Route::put('/fleet/maintenance-schedule/{Id}/cancel', [FleetMaintenanceScheduleController::class, 'cancel'])->name('fleet.maintenance_schedule.cancel');

        // ==================== Repair Logs ====================
        Route::get('/fleet/repair-logs', [FleetRepairLogController::class, 'index'])->name('fleet.repair_logs.index');
        Route::get('/fleet/repair-logs/create', [FleetRepairLogController::class, 'create'])->name('fleet.repair_logs.create');
        Route::post('/fleet/repair-logs', [FleetRepairLogController::class, 'store'])->name('fleet.repair_logs.store');
        Route::get('/fleet/repair-logs/{Id}/edit', [FleetRepairLogController::class, 'edit'])->name('fleet.repair_logs.edit');
        Route::put('/fleet/repair-logs/{Id}', [FleetRepairLogController::class, 'update'])->name('fleet.repair_logs.update');
        Route::get('/fleet/repair-logs/{Id}/show', [FleetRepairLogController::class, 'show'])->name('fleet.repair_logs.show');

        // ==================== Service Alerts ====================
        Route::get('/fleet/service-alerts', [FleetServiceAlertController::class, 'index'])->name('fleet.alerts.index');
        Route::put('/fleet/service-alerts/{Id}/acknowledge', [FleetServiceAlertController::class, 'acknowledge'])->name('fleet.alerts.acknowledge');

        // ==================== Alert Rules ====================
        Route::get('/fleet/alert-rules', [FleetAlertRuleController::class, 'index'])->name('fleet.alert_rules.index');
        Route::get('/fleet/alert-rules/create', [FleetAlertRuleController::class, 'create'])->name('fleet.alert_rules.create');
        Route::post('/fleet/alert-rules', [FleetAlertRuleController::class, 'store'])->name('fleet.alert_rules.store');
        Route::get('/fleet/alert-rules/{Id}/edit', [FleetAlertRuleController::class, 'edit'])->name('fleet.alert_rules.edit');
        Route::put('/fleet/alert-rules/{Id}', [FleetAlertRuleController::class, 'update'])->name('fleet.alert_rules.update');
        Route::put('/fleet/alert-rules/{Id}/toggle', [FleetAlertRuleController::class, 'toggle'])->name('fleet.alert_rules.toggle');

        // ==================== Running Costs ====================
        Route::get('/fleet/running-costs', [FleetRunningCostController::class, 'index'])->name('fleet.running_costs.index');
        Route::get('/fleet/running-costs/create', [FleetRunningCostController::class, 'create'])->name('fleet.running_costs.create');
        Route::post('/fleet/running-costs', [FleetRunningCostController::class, 'store'])->name('fleet.running_costs.store');

        // ==================== GPS ====================
        Route::get('/fleet/gps/live', [FleetGpsController::class, 'liveDashboard'])->name('fleet.gps.live_dashboard')->middleware('auth');
        Route::get('/fleet/gps/movement-history', [FleetGpsController::class, 'movementHistory'])->name('fleet.gps.movement_history')->middleware('auth');

        // ==================== Telematics ====================
        Route::get('/fleet/telematics', [FleetTelematicsDeviceController::class, 'index'])->name('fleet.telematics.index')->middleware('auth');
        Route::get('/fleet/telematics/create', [FleetTelematicsDeviceController::class, 'create'])->name('fleet.telematics.create')->middleware('auth');
        Route::post('/fleet/telematics', [FleetTelematicsDeviceController::class, 'store'])->name('fleet.telematics.store')->middleware('auth');

        // ==================== Insurance Tracker ====================
        Route::get('/fleet/insurance-tracker', [FleetInsuranceTrackerController::class, 'index'])->name('fleet.insurance_tracker.index')->middleware('auth');
        Route::get('/fleet/insurance-tracker/create', [FleetInsuranceTrackerController::class, 'create'])->name('fleet.insurance_tracker.create')->middleware('auth');
        Route::post('/fleet/insurance-tracker', [FleetInsuranceTrackerController::class, 'store'])->name('fleet.insurance_tracker.store')->middleware('auth');

        // ==================== Inspection Schedule ====================
        Route::get('/fleet/compliance/inspection-schedule', [FleetInspectionScheduleController::class, 'index'])->name('fleet.inspection_schedule.index');
        Route::get('/fleet/compliance/inspection-schedule/create', [FleetInspectionScheduleController::class, 'create'])->name('fleet.inspection_schedule.create');
        Route::post('/fleet/compliance/inspection-schedule', [FleetInspectionScheduleController::class, 'store'])->name('fleet.inspection_schedule.store');
