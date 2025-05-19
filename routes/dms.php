<?php

use App\Http\Controllers\DMS\AccessManagementController;
use App\Http\Controllers\DMS\CategoriesManagementController;
use App\Http\Controllers\DMS\DrepositoryManagementController;
use App\Http\Controllers\DMS\DtypessetupManagementController;
use App\Http\Controllers\DMS\RenewalManagementController;
use App\Http\Controllers\DMS\SearchManagementController;
use App\Http\Controllers\DMS\TrailManagementController;
use App\Http\Controllers\DMS\UploadManagementController;
use App\Http\Controllers\DMS\VersioncontrolManagementController;
use Illuminate\Support\Facades\Route;


Route::namespace('DMS')->prefix('dms')->group(function () {
    Route::resource('drepositorymanagement', DrepositoryManagementController::class);
    Route::resource('dtypessetupmanagement', DtypessetupManagementController::class);
    Route::resource('categoriesmanagement', CategoriesManagementController::class);
    Route::resource('versioncontrolmanagement', VersioncontrolManagementController::class);
    Route::resource('searchmanagement', SearchManagementController::class);
    Route::resource('renewalmanagement', RenewalManagementController::class);
    Route::resource('accessmanagement', AccessManagementController::class);
    Route::resource('trailmanagement', TrailManagementController::class);
    Route::resource('uploadmanagement', UploadManagementController::class);


});
