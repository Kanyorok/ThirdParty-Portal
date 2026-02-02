<?php

use App\Http\Controllers\API\Property\PropertyInvoiceController;
use App\Http\Controllers\API\Property\PropertyLeaseController;
use App\Http\Controllers\API\Property\PropertyMaintenanceRequestController;
use App\Http\Controllers\API\Property\PropertyViewController;
use Illuminate\Support\Facades\Route;

// Property View Routes
Route::get('property/rentable-properties', [PropertyViewController::class, 'index']);

// Property Lease Routes
Route::get('property/leases/tenant', [PropertyLeaseController::class, 'index']);
Route::get('property/leases/tenant/show', [PropertyLeaseController::class, 'show']);

// Property Invoice Routes
Route::get('property/invoices/tenant', [PropertyInvoiceController::class, 'index']);
Route::get('property/invoices/tenant/show', [PropertyInvoiceController::class, 'show']);


// Maintenance Request Routes
Route::get('property/maintenancerequest', [PropertyMaintenanceRequestController::class, 'index']);
Route::post('property/maintenancerequest', [PropertyMaintenanceRequestController::class, 'store']);
Route::get('property/maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'show']);
Route::delete('property/maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'destroy']);
