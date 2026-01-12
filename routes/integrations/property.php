<?php
use App\Http\Controllers\API\Property\PropertyInvoiceController;
use App\Http\Controllers\API\Property\PropertyLeaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Property\PropertyViewController;
use App\Http\Controllers\API\Property\PropertyMaintenanceRequestController;

// Route::prefix('property')->middleware(PropertyAuthMiddleware::class)->group(function () {
//     Route::get('rentable-properties', [PropertyViewController::class, 'rentableProperties']);
//     // Route::get('property-structure/{id}', [PropertyViewController::class, 'propertyStructure']);
// });

// Property View Routes
Route::get('property/rentable-properties', [PropertyViewController::class, 'index']);

// Property Lease Routes
Route::get('property/leases/tenant', [PropertyLeaseController::class, 'index']);
Route::get('property/leases/tenant/show',[PropertyLeaseController::class, 'show']);

// Property Invoice Routes
Route::get('property/invoices', [PropertyInvoiceController::class, 'index']);
Route::get('property/invoices/{id}', [PropertyInvoiceController::class, 'show']);

// Maintenance Request Routes
Route::get('property/maintenancerequest', [PropertyMaintenanceRequestController::class, 'index']);
Route::post('property/maintenancerequest', [PropertyMaintenanceRequestController::class, 'store']);
Route::get('property/maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'show']);
Route::delete('property/maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'destroy']);