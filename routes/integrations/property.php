<?php

use App\Http\Controllers\API\Property\PropertyInterestController;
use App\Http\Controllers\API\Property\PropertyInvoiceController;
use App\Http\Controllers\API\Property\PropertyLeaseController;
use App\Http\Controllers\API\Property\PropertyMaintenanceRequestController;
use App\Http\Controllers\API\Property\PropertyViewController;
use Illuminate\Support\Facades\Route;

Route::prefix('property')->group(function () {
    // Property View Routes
    Route::get('rentable-properties', [PropertyViewController::class, 'index']);

    // Property Lease Routes
    Route::get('leases/tenant', [PropertyLeaseController::class, 'index']);
    Route::get('leases/tenant/show', [PropertyLeaseController::class, 'show']);

    // Property Invoice Routes
    Route::get('invoices/tenant', [PropertyInvoiceController::class, 'index']);
    Route::get('invoices/tenant/show', [PropertyInvoiceController::class, 'show']);

    // Property Interest Routes
    Route::get('lease-interests', [PropertyInterestController::class, 'index']);
    Route::post('lease-interests', [PropertyInterestController::class, 'store']);
    Route::get('lease-interests/show', [PropertyInterestController::class, 'show']);
    Route::delete('lease-interests/delete', [PropertyInterestController::class, 'destroy']);


    // Maintenance Request Routes
    Route::get('maintenancerequest', [PropertyMaintenanceRequestController::class, 'index']);
    Route::post('maintenancerequest', [PropertyMaintenanceRequestController::class, 'store']);
    Route::get('maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'show']);
    Route::delete('maintenancerequest/{id}', [PropertyMaintenanceRequestController::class, 'destroy']);

});
