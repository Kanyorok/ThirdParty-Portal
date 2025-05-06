<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Insurance\ProviderManagementController;
use App\Http\Controllers\Insurance\InsurancetypeManagementController;
use App\Http\Controllers\Insurance\ContactManagementController;


Route::namespace('Insurance')->group(function () {
    Route::resource('providermanagement', ProviderManagementController::class);
    Route::resource('insurancetypemanagement', InsurancetypeManagementController::class);
    Route::resource('contactmanagement', ContactManagementController::class);
    

});