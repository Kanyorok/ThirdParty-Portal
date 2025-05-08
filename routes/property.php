<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Property\AddPropertyController;
use App\Http\Controllers\Property\PropertyTypeController;
use App\Http\Controllers\Property\PropertyCategoryController;
use App\Http\Controllers\Property\PropertyBlockController;
use App\Http\Controllers\Property\PropertyFloorController;
use App\Http\Controllers\Property\PropertyUnitController;
use App\Http\Controllers\Property\PropertyAttachmentsController;
use App\Http\Controllers\Property\PropertyNewTenantController;
use App\Http\Controllers\Property\PropertyNewLeaseController;
use App\Http\Controllers\Property\PropertyLeaseScheduleController;

Route::namespace('Property')->group(function () {
    Route::resource('addproperty', AddPropertyController::class);
    Route::resource('propertytype', PropertyTypeController::class);
    Route::resource('propertycategory', PropertyCategoryController::class);
    Route::resource('addblock', PropertyBlockController::class);
    Route::resource('addfloor', PropertyFloorController::class);
    Route::resource('addunit', PropertyUnitController::class);
    Route::resource('attachments', PropertyAttachmentsController::class);
    Route::resource('addtenant', PropertyNewTenantController::class);
    Route::resource('addlease', PropertyNewLeaseController::class);
    Route::resource('schedulelease', PropertyLeaseScheduleController::class);
});
