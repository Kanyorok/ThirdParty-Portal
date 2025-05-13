<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentmanagement\DrepositoryManagementController;
use App\Http\Controllers\Documentmanagement\DtypessetupManagementController;
use App\Http\Controllers\Documentmanagement\CategoriesManagementController;
use App\Http\Controllers\Documentmanagement\VersioncontrolManagementController;
use App\Http\Controllers\Documentmanagement\SearchManagementController;
use App\Http\Controllers\Documentmanagement\RenewalManagementController;
use App\Http\Controllers\Documentmanagement\AccessManagementController;
use App\Http\Controllers\Documentmanagement\TrailManagementController;
use App\Http\Controllers\Documentmanagement\UploadManagementController;


Route::namespace('Documentmanagement')->group(function () {
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