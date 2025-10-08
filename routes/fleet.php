<?php

use App\Http\Controllers\Fleet\ContractedDriverController;
use App\Http\Controllers\Fleet\FleetAlertRuleController;
use App\Http\Controllers\Fleet\FleetContractedDriverAssignmentController;
use App\Http\Controllers\Fleet\FleetContractedDriverLicenseTrackingController;
use App\Http\Controllers\Fleet\FleetDriverAssignmentController;
use App\Http\Controllers\Fleet\FleetDriverController;
use App\Http\Controllers\Fleet\FleetDriverLicenseTrackingController;
use App\Http\Controllers\Fleet\FleetFuelLogController;
use App\Http\Controllers\Fleet\FleetGpsController;
use App\Http\Controllers\Fleet\FleetInspectionScheduleController;
use App\Http\Controllers\Fleet\FleetInsuranceTrackerController;
use App\Http\Controllers\Fleet\FleetMaintenanceScheduleController;
use App\Http\Controllers\Fleet\FleetRepairLogController;
use App\Http\Controllers\Fleet\FleetRoutePlannerController;
use App\Http\Controllers\Fleet\FleetRunningCostController;
use App\Http\Controllers\Fleet\FleetServiceAlertController;
use App\Http\Controllers\Fleet\FleetTelematicsDeviceController;
use App\Http\Controllers\Fleet\FleetTripLogController;
use App\Http\Controllers\Fleet\FleetVehicleInspectionController;
use App\Http\Controllers\Fleet\FleetVehicleAssignmentController;
use App\Http\Controllers\Fleet\FleetVehicleRequestController;
use App\Http\Controllers\Fleet\VehicleController;
use App\Http\Controllers\Fleet\VehicleDocumentController;
use App\Http\Controllers\FleetManagement\ComplianceAndDocumentationController;
use App\Http\Controllers\FleetManagement\ReportsController;
use App\Http\Controllers\FleetManagement\DriverManagementController;
use App\Http\Controllers\FleetManagement\FleetMakeController;
use App\Http\Controllers\FleetManagement\FleetModelController;
use App\Http\Controllers\FleetManagement\FleetProcurementAndDisposalController;
use App\Http\Controllers\FleetManagement\FuelManagementController;
use App\Http\Controllers\FleetManagement\FuelTypeController;
use App\Http\Controllers\FleetManagement\InventoryOfSparePartsController;
use App\Http\Controllers\FleetManagement\LicensingController;
use App\Http\Controllers\FleetManagement\ServiceTrackingController;
use App\Http\Controllers\FleetManagement\TripManagementController;
use App\Http\Controllers\FleetManagement\UtilizationController;
use App\Http\Controllers\FleetManagement\VehicleManagementController;
use Illuminate\Support\Facades\Route;


Route::namespace('Fleet')->prefix('fleet')->group(function () {
    Route::namespace('Fleet')->name('fleet.')->group(function () {

        // ==================== Route Planner ====================
        Route::resource('route_planner', FleetRoutePlannerController::class)->only(['index', 'create', 'store']);
    });
    /*Route::get('/r', [, 'index'])->name('fleet.route_planner.index');
    Route::get('/route-planner/create', [FleetRoutePlannerController::class, 'create'])->name('fleet.route_planner.create');
    Route::post('/route-planner', [FleetRoutePlannerController::class, 'store'])->name('fleet.route_planner.store');*/


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

    // ==================== Fleet Vehicles ====================
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('fleet.vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('fleet.vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('fleet.vehicles.store');
    Route::get('/vehicles/{Id}', [VehicleController::class, 'show'])->name('fleet.vehicles.show');
    Route::get('/vehicles/{Id}/edit', [VehicleController::class, 'edit'])->name('fleet.vehicles.edit');
    Route::put('/vehicles/{Id}', [VehicleController::class, 'update'])->name('fleet.vehicles.update');
    Route::delete('/vehicles/{Id}', [VehicleController::class, 'destroy'])->name('fleet.vehicles.destroy');
    Route::delete('/vehicles/{Id}/deactivate', [VehicleController::class, 'deactivate'])->name('fleet.vehicles.deactivate');
    Route::get('/models-by-make/{Id}', [VehicleController::class, 'getByMake'])
        ->name('models.byMake');


    // ==================== Fleet Vehicle Assignments ====================
    Route::get('/assignments', [FleetVehicleAssignmentController::class, 'index'])->name('fleet.assignments.index');
    Route::get('/assignments/create', [FleetVehicleAssignmentController::class, 'create'])->name('fleet.assignments.create');
    Route::post('/assignments', [FleetVehicleAssignmentController::class, 'store'])->name('fleet.assignments.store');
    Route::get('/assignments/{Id}/edit', [FleetVehicleAssignmentController::class, 'edit'])->name('fleet.assignments.edit');
    Route::get('/assignments/{Id}', [FleetVehicleAssignmentController::class, 'show'])->name('fleet.assignments.show');
    Route::put('/assignments/{Id}', [FleetVehicleAssignmentController::class, 'update'])->name('fleet.assignments.update');
    Route::delete('/assignments/{Id}', [FleetVehicleAssignmentController::class, 'destroy'])->name('fleet.assignments.destroy');
    Route::get('/assignments/get-vehicles/{Id}', [FleetVehicleAssignmentController::class, 'getVehiclesByTrip'])->name('fleet.assignments.getVehicles');
    Route::get('/assignments/get-inspection/{Id}', [FleetVehicleAssignmentController::class, 'getVehicleInspection'])->name('fleet.assignments.getInspection');
    Route::get('/assignments/get-driver/{Id}', [FleetVehicleAssignmentController::class, 'getVehicleDriver'])->name('fleet.assignments.getDriver');




    // ==================== Fleet Vehicle Documents ====================
    Route::get('/documents', [VehicleDocumentController::class, 'index'])->name('fleet.documents.index');
    Route::get('/documents/create', [VehicleDocumentController::class, 'create'])->name('fleet.documents.create');
    Route::post('/documents', [VehicleDocumentController::class, 'store'])->name('fleet.documents.store');

    // ==================== Fleet Drivers ====================
    Route::get('/drivers', [FleetDriverController::class, 'index'])->name('fleet.drivers.index');
    Route::get('/drivers/create', [FleetDriverController::class, 'create'])->name('fleet.drivers.create');
    Route::post('/drivers', [FleetDriverController::class, 'store'])->name('fleet.drivers.store');
    Route::get('/drivers/{Id}/edit', [FleetDriverController::class, 'edit'])->name('fleet.drivers.edit');
    Route::get('/drivers/{Id}', [FleetDriverController::class, 'show'])->name('fleet.drivers.show');
    Route::put('/drivers/{Id}', [FleetDriverController::class, 'update'])->name('fleet.drivers.update');
    Route::delete('/drivers/{Id}', [FleetDriverController::class, 'destroy'])->name('fleet.drivers.destroy');

    // ==================== Fleet Driver Assignments ====================
    Route::get('/driver-assignments', [FleetDriverAssignmentController::class, 'index'])->name('fleet.driver_assignments.index');
    Route::get('/driver-assignments/create', [FleetDriverAssignmentController::class, 'create'])->name('fleet.driver_assignments.create');
    Route::post('/driver-assignments', [FleetDriverAssignmentController::class, 'store'])->name('fleet.driver_assignments.store');
    Route::get('/driver-assignments/{Id}/edit', [FleetDriverAssignmentController::class, 'edit'])->name('fleet.driver_assignments.edit');
    Route::get('/driver-assignments/{Id}', [FleetDriverAssignmentController::class, 'show'])->name('fleet.driver_assignments.show');
    Route::put('/driver-assignments/{Id}', [FleetDriverAssignmentController::class, 'update'])->name('fleet.driver_assignments.update');
    Route::delete('/driver-assignments/{Id}', [FleetDriverAssignmentController::class, 'destroy'])->name('fleet.driver_assignments.destroy');

    Route::get('/driver-assignments/{Id}/assign-inspection', [FleetDriverAssignmentController::class, 'assignInspection'])->name('fleet.driver_assignments.assign_inspection');
    Route::get('/driver-assignments/{Id}/unassign-inspection', [FleetDriverAssignmentController::class, 'unassignInspection'])->name('fleet.driver_assignments.unassign_inspection');


    // ==================== Fleet Driver License Tracking ====================

    Route::get('/driver-licenses', [FleetDriverLicenseTrackingController::class, 'index'])->name('fleet.licenses.index');
    Route::get('/driver-licenses/create', [FleetDriverLicenseTrackingController::class, 'create'])->name('fleet.licenses.create');
    Route::post('/driver-licenses', [FleetDriverLicenseTrackingController::class, 'store'])->name('fleet.licenses.store');
    Route::get('/driver-licenses/{Id}/edit', [FleetDriverLicenseTrackingController::class, 'edit'])->name('licenses.drivers.edit');
    Route::get('/driver-licenses/{Id}', [FleetDriverLicenseTrackingController::class, 'show'])->name('fleet.licenses.show');
    Route::put('/driver-licenses/{Id}', [FleetDriverLicenseTrackingController::class, 'update'])->name('fleet.licenses.update');
    Route::delete('/driver-licenses/{Id}', [FleetDriverLicenseTrackingController::class, 'destroy'])->name('fleet.licenses.destroy');

    // ==================== Contracted Drivers ====================
    Route::get('/contracted-drivers', [ContractedDriverController::class, 'index'])->name('fleet.contracted_drivers.index');
    Route::get('/contracted-drivers/create', [ContractedDriverController::class, 'create'])->name('fleet.contracted_drivers.create');
    Route::post('/contracted-drivers', [ContractedDriverController::class, 'store'])->name('fleet.contracted_drivers.store');
    Route::get('/contracted-drivers/{Id}/edit', [ContractedDriverController::class, 'edit'])->name('fleet.contracted_drivers.edit');
    Route::get('/contracted-drivers/{Id}', [ContractedDriverController::class, 'show'])->name('fleet.contracted_drivers.show');
    Route::put('/contracted-drivers/{Id}', [ContractedDriverController::class, 'update'])->name('fleet.contracted_drivers.update');
    Route::delete('/contracted-drivers/{Id}', [ContractedDriverController::class, 'destroy'])->name('fleet.contracted_drivers.destroy');
    Route::put('/contracted-drivers/{Id}/deactivate', [ContractedDriverController::class, 'deactivate'])->name('fleet.contracted_drivers.deactivate');

    // ==================== Contracted Driver Licenses ====================
    Route::get('/contracted-drivers/{Id}/licenses', [FleetContractedDriverLicenseTrackingController::class, 'index'])->name('fleet.contracted_driver_licenses.index');
    Route::get('/contracted-drivers/{Id}/licenses/create', [FleetContractedDriverLicenseTrackingController::class, 'create'])->name('fleet.contracted_driver_licenses.create');
    Route::post('/contracted-drivers/{Id}/licenses', [FleetContractedDriverLicenseTrackingController::class, 'store'])->name('fleet.contracted_driver_licenses.store');
    Route::get('/contracted-drivers/{driverId}/licenses/{licenseId}/edit', [FleetContractedDriverLicenseTrackingController::class, 'edit'])->name('fleet.contracted_driver_licenses.edit');
    Route::put('/contracted-drivers/{driverId}/licenses/{licenseId}', [FleetContractedDriverLicenseTrackingController::class, 'update'])->name('fleet.contracted_driver_licenses.update');
    Route::delete('/contracted-drivers/{driverId}/licenses/{licenseId}', [FleetContractedDriverLicenseTrackingController::class, 'destroy'])->name('fleet.contracted_driver_licenses.destroy');

    // ==================== Contracted Driver Assignments ====================
    Route::get('/contracted-drivers/{driverId}/assignments', [FleetContractedDriverAssignmentController::class, 'index'])->name('fleet.contracted_driver_assignments.index');
    Route::get('/contracted-drivers/{driverId}/assignments/create', [FleetContractedDriverAssignmentController::class, 'create'])->name('fleet.contracted_driver_assignments.create');
    Route::post('/contracted-drivers/{driverId}/assignments', [FleetContractedDriverAssignmentController::class, 'store'])->name('fleet.contracted_driver_assignments.store');
    Route::get('/contracted-drivers/{driverId}/assignments/{assignmentId}/edit', [FleetContractedDriverAssignmentController::class, 'edit'])->name('fleet.contracted_driver_assignments.edit');
    Route::put('/contracted-drivers/{driverId}/assignments/{assignmentId}', [FleetContractedDriverAssignmentController::class, 'update'])->name('fleet.contracted_driver_assignments.update');
    Route::delete('/contracted-drivers/{driverId}/assignments/{assignmentId}', [FleetContractedDriverAssignmentController::class, 'destroy'])->name('fleet.contracted_driver_assignments.destroy');

    // ==================== Trip Logs ====================
    Route::get('/trip-logs', [FleetTripLogController::class, 'index'])->name('fleet.trip_logs.index');
    Route::get('/trip-logs/create', [FleetTripLogController::class, 'create'])->name('fleet.trip_logs.create');
    Route::post('/trip-logs', [FleetTripLogController::class, 'store'])->name('fleet.trip_logs.store');
    Route::get('/trip-logs/approved-transfers', [FleetTripLogController::class, 'getApprovedTransfers'])->name('fleet.trip_logs.approved_transfers');
    Route::get('/trip-logs/approved-campaigns', [FleetTripLogController::class, 'getApprovedCampaigns'])->name('fleet.trip_logs.approved_campaigns');
    Route::get('/trip-logs/{Id}', [FleetTripLogController::class, 'show'])->name('fleet.trip_logs.show');
    Route::get('/trip-logs/{Id}/edit', [FleetTripLogController::class, 'edit'])->name('fleet.trip_logs.edit');
    Route::put('/trip-logs/{Id}', [FleetTripLogController::class, 'update'])->name('fleet.trip_logs.update');
    Route::delete('/trip-logs/{Id}', [FleetTripLogController::class, 'destroy'])->name('fleet.trip_logs.destroy');
    Route::get('fleet/vehicles/available', [FleetTripLogController::class, 'getAvailableVehicles'])->name('fleet.vehicles.available');
    Route::get('fleet/drivers/available', [FleetTripLogController::class, 'getAvailablePermanentDrivers'])->name('fleet.drivers.available');
    Route::get('fleet/contracted_drivers/available', [FleetTripLogController::class, 'getAvailableContractedDrivers'])->name('fleet.contracted_drivers.available');
    



     // ==================== Vehicle Inspections====================
    Route::get('/vehicle_inspection', [FleetVehicleInspectionController::class, 'index'])->name('fleet.vehicle_inspection.index');
    Route::get('/vehicle_inspection/create', [FleetVehicleInspectionController::class, 'create'])->name('fleet.vehicle_inspection.create');
    Route::post('/vehicle_inspection', [FleetVehicleInspectionController::class, 'store'])->name('fleet.vehicle_inspection.store');
    Route::get('/vehicle_inspection{Id}', [FleetVehicleInspectionController::class, 'show'])->name('fleet.vehicle_inspection.show');
    Route::get('/vehicle_inspection{Id}/edit', [FleetVehicleInspectionController::class, 'edit'])->name('fleet.vehicle_inspection.edit');
    Route::put('vehicle_inspection{Id}', [FleetVehicleInspectionController::class, 'update'])->name('fleet.vehicle_inspection.update');
    Route::delete('vehicle_inspection{Id}', [FleetVehicleInspectionController::class, 'destroy'])->name('fleet.vehicle_inspection.destroy');
    Route::get('/vehicle_inspection/{Id}/posttrip', [FleetVehicleInspectionController::class, 'createPostTrip'])->name('fleet.vehicle_inspection.posttrip.create');



    // ==================== Fuel Logs ====================
    Route::get('/fuel-logs', [FleetFuelLogController::class, 'index'])->name('fleet.fuel_logs.index');
    Route::get('/fuel-logs/create', [FleetFuelLogController::class, 'create'])->name('fleet.fuel_logs.create');
    Route::post('/fuel-logs', [FleetFuelLogController::class, 'store'])->name('fleet.fuel_logs.store');
    Route::get('/fuel-logs{Id}', [FleetFuelLogController::class, 'show'])->name('fleet.fuel_logs.show');

    // ==================== Vehicle Requests ====================
    Route::get('/vehicle-requests', [FleetVehicleRequestController::class, 'index'])->name('fleet.vehicle_requests.index');
    Route::get('/vehicle-requests/create', [FleetVehicleRequestController::class, 'create'])->name('fleet.vehicle_requests.create');
    Route::post('/vehicle-requests', [FleetVehicleRequestController::class, 'store'])->name('fleet.vehicle_requests.store');
    Route::get('/vehicle-requests/{Id}/approve', [FleetVehicleRequestController::class, 'approveForm'])->name('fleet.vehicle_requests.approve.form');
    Route::post('/vehicle-requests/{Id}/approve', [FleetVehicleRequestController::class, 'approve'])->name('fleet.vehicle_requests.approve');
    Route::put('/vehicle-requests/{Id}/cancel', [FleetVehicleRequestController::class, 'cancel'])->name('fleet.vehicle_requests.cancel');

    // ==================== Maintenance Schedule ====================
    Route::get('/maintenance-schedule', [FleetMaintenanceScheduleController::class, 'index'])->name('fleet.maintenance_schedule.index');
    Route::get('/maintenance-schedule/create', [FleetMaintenanceScheduleController::class, 'create'])->name('fleet.maintenance_schedule.create');
    Route::post('/maintenance-schedule', [FleetMaintenanceScheduleController::class, 'store'])->name('fleet.maintenance_schedule.store');
    Route::get('/maintenance-schedule/{Id}/edit', [FleetMaintenanceScheduleController::class, 'edit'])->name('fleet.maintenance_schedule.edit');
    Route::put('/maintenance-schedule/{Id}', [FleetMaintenanceScheduleController::class, 'update'])->name('fleet.maintenance_schedule.update');
    Route::get('/maintenance-schedule/{Id}', [FleetMaintenanceScheduleController::class, 'show'])->name('fleet.maintenance_schedule.show');
    Route::get('/maintenance-schedule/{Id}', [FleetMaintenanceScheduleController::class, 'show'])->name('fleet.maintenance_schedule.show');
    Route::put('/maintenance-schedule/{Id}/cancel', [FleetMaintenanceScheduleController::class, 'cancel'])->name('fleet.maintenance_schedule.cancel');

    // ==================== Repair Logs ====================
    Route::get('/repair-logs', [FleetRepairLogController::class, 'index'])->name('fleet.repair_logs.index');
    Route::get('/repair-logs/create', [FleetRepairLogController::class, 'create'])->name('fleet.repair_logs.create');
    Route::post('/repair-logs', [FleetRepairLogController::class, 'store'])->name('fleet.repair_logs.store');
    Route::get('/repair-logs/{Id}/edit', [FleetRepairLogController::class, 'edit'])->name('fleet.repair_logs.edit');
    Route::put('/repair-logs/{Id}', [FleetRepairLogController::class, 'update'])->name('fleet.repair_logs.update');
    Route::get('/repair-logs/{Id}/show', [FleetRepairLogController::class, 'show'])->name('fleet.repair_logs.show');
    Route::delete('/repair-logs/{Id}', [FleetRepairLogController::class, 'destroy'])->name('fleet.repair_logs.destroy');

    Route::delete('/repair-logs/{Id}', [FleetRepairLogController::class, 'destroy'])->name('fleet.repair_logs.destroy');


    // ==================== Service Alerts ====================
    Route::get('/service-alerts', [FleetServiceAlertController::class, 'index'])->name('fleet.alerts.index');
    Route::put('/service-alerts/{Id}/acknowledge', [FleetServiceAlertController::class, 'acknowledge'])->name('fleet.alerts.acknowledge');

    // ==================== Alert Rules ====================
    Route::get('/alert-rules', [FleetAlertRuleController::class, 'index'])->name('fleet.alert_rules.index');
    Route::get('/alert-rules/create', [FleetAlertRuleController::class, 'create'])->name('fleet.alert_rules.create');
    Route::post('/alert-rules', [FleetAlertRuleController::class, 'store'])->name('fleet.alert_rules.store');
    Route::get('/alert-rules/{Id}/edit', [FleetAlertRuleController::class, 'edit'])->name('fleet.alert_rules.edit');
    Route::put('/alert-rules/{Id}', [FleetAlertRuleController::class, 'update'])->name('fleet.alert_rules.update');
    Route::put('/alert-rules/{Id}/toggle', [FleetAlertRuleController::class, 'toggle'])->name('fleet.alert_rules.toggle');

    // ==================== Running Costs ====================
    Route::get('/running-costs', [FleetRunningCostController::class, 'index'])->name('fleet.running_costs.index');
    Route::get('/running-costs/create', [FleetRunningCostController::class, 'create'])->name('fleet.running_costs.create');
    Route::post('/running-costs', [FleetRunningCostController::class, 'store'])->name('fleet.running_costs.store');

    // ==================== GPS ====================
    Route::get('/gps/live', [FleetGpsController::class, 'liveDashboard'])->name('fleet.gps.live_dashboard');
    Route::get('/gps/movement-history', [FleetGpsController::class, 'movementHistory'])->name('fleet.gps.movement_history');

    // ==================== Telematics ====================
    Route::get('/telematics', [FleetTelematicsDeviceController::class, 'index'])->name('fleet.telematics.index');
    Route::get('/telematics/create', [FleetTelematicsDeviceController::class, 'create'])->name('fleet.telematics.create');
    Route::post('/telematics', [FleetTelematicsDeviceController::class, 'store'])->name('fleet.telematics.store');

    // ==================== Insurance Tracker ====================
    Route::get('/insurance-tracker', [FleetInsuranceTrackerController::class, 'index'])->name('fleet.insurance_tracker.index');
    Route::get('/insurance-tracker/create', [FleetInsuranceTrackerController::class, 'create'])->name('fleet.insurance_tracker.create');
    Route::post('/insurance-tracker', [FleetInsuranceTrackerController::class, 'store'])->name('fleet.insurance_tracker.store');
    Route::get('/insurance-tracker/{Id}', [FleetInsuranceTrackerController::class, 'show'])->name('fleet.insurance_tracker.show');
    Route::get('/insurance-tracker{Id}/edit', [FleetInsuranceTrackerController::class, 'edit'])->name('fleet.insurance_tracker.edit');
    Route::put('insurance-tracker/{Id}', [FleetInsuranceTrackerController::class, 'update'])->name('fleet.insurance_tracker.update');
    Route::delete('insurance-tracker/{Id}', [FleetInsuranceTrackerController::class, 'destroy'])->name('fleet.insurance_tracker.destroy');

    // ==================== Inspection Schedule ====================
    Route::get('/compliance/inspection-schedule', [FleetInspectionScheduleController::class, 'index'])->name('fleet.inspection_schedule.index');
    Route::get('/compliance/inspection-schedule/create', [FleetInspectionScheduleController::class, 'create'])->name('fleet.inspection_schedule.create');
    Route::post('/compliance/inspection-schedule', [FleetInspectionScheduleController::class, 'store'])->name('fleet.inspection_schedule.store');
    Route::get('/compliance/inspection-schedule/{Id}', [FleetInspectionScheduleController::class, 'show'])->name('fleet.inspection_schedule.show');
    Route::get('/compliance/inspection-schedule{Id}/edit', [FleetInspectionScheduleController::class, 'edit'])->name('fleet.inspection_schedule.edit');
    Route::put('compliance/inspection-schedule/{Id}', [FleetInspectionScheduleController::class, 'update'])->name('fleet.inspection_schedule.update');
    Route::delete('compliance/inspection-schedule/{Id}', [FleetInspectionScheduleController::class, 'destroy'])->name('fleet.inspection_schedule.destroy');

    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('fleet-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'fleet-reports.index',
        'show' => 'fleet-reports.show'
    ]);
});
