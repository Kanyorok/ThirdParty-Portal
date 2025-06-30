<?php

use App\Http\Controllers\DMS\AccessManagementController;
use App\Http\Controllers\DMS\CategoriesManagementController;
use App\Http\Controllers\DMS\DrepositoryManagementController;
use App\Http\Controllers\DMS\DtypessetupManagementController;
use App\Http\Controllers\DMS\Files\DocumentActivityController;
use App\Http\Controllers\DMS\Files\DocumentController;
use App\Http\Controllers\DMS\Files\DocumentPermissionController;
use App\Http\Controllers\DMS\RenewalManagementController;
use App\Http\Controllers\DMS\Repo\RepositoryController;
use App\Http\Controllers\DMS\Repo\RepositoryPermissionController;
use App\Http\Controllers\DMS\SearchManagementController;
use App\Http\Controllers\DMS\TrailManagementController;
use App\Http\Controllers\DMS\UploadManagementController;
use App\Http\Controllers\DMS\VersioncontrolManagementController;
use Illuminate\Support\Facades\Route;


Route::namespace('DMS')->prefix('dms')->group(function () {

    Route::put('repo/{repository}/repo-visibility', [RepositoryPermissionController::class, 'visibility'])->name('repo.visibility');
    Route::resource('repo/{repository}/repo-permissions', RepositoryPermissionController::class)->only(['index', 'store', 'destroy']);
    Route::resource('repo', RepositoryController::class)->parameters(['repo' => 'repository']);

    Route::get('document/{document}/activities', DocumentActivityController::class)->name('file.activities');
    Route::get('document/{document}/preview', [DocumentController::class, 'preview'])->name('file.preview');
    Route::put('document/{document}/file-visibility', [DocumentPermissionController::class, 'visibility'])->name('file.visibility');
    Route::resource('document/{document}/file-permissions', DocumentPermissionController::class)->only(['index', 'store', 'destroy']);
    Route::resource('repo/{repository}/files', DocumentController::class)
        ->parameters(['files' => 'document'])->except('create');

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
