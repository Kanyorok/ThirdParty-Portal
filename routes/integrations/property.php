<?php
use App\Http\Controllers\API\Property\PropertyInvoiceController;
use App\Http\Controllers\API\Property\PropertyLeaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Property\PropertyViewController;

// Route::prefix('property')->middleware(PropertyAuthMiddleware::class)->group(function () {
//     Route::get('rentable-properties', [PropertyViewController::class, 'rentableProperties']);
//     // Route::get('property-structure/{id}', [PropertyViewController::class, 'propertyStructure']);
// });

Route::get('property/rentable-properties', [PropertyViewController::class, 'index']);
Route::get('property/leases', [PropertyLeaseController::class, 'index']);
Route::get('invoices', [PropertyInvoiceController::class, 'index']);
Route::get('invoices/{id}', [PropertyInvoiceController::class, 'show']);

use App\Http\Controllers\API\Property\PropertyMaintenanceRequestController;
Route::get('property/maintenancerequest', [PropertyMaintenanceRequestController::class, 'index']);
Route::post('property/maintenancerequest', [PropertyMaintenanceRequestController::class, 'store']);
Route::get('property/maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'show']);
Route::delete('property/maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'destroy']);