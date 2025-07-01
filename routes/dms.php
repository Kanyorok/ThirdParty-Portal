<?php

use App\Http\Controllers\DMS\AccessManagementController;
use App\Http\Controllers\DMS\CategoriesManagementController;
use App\Http\Controllers\DMS\DocumentRecentController;
use App\Http\Controllers\DMS\DrepositoryManagementController;
use App\Http\Controllers\DMS\DtypessetupManagementController;
use App\Http\Controllers\DMS\Files\DocumentActionsConroller;
use App\Http\Controllers\DMS\Files\DocumentActivityController;
use App\Http\Controllers\DMS\Files\DocumentController;
use App\Http\Controllers\DMS\Files\DocumentPermissionController;
use App\Http\Controllers\DMS\Files\DocumentUploadController;
use App\Http\Controllers\DMS\RenewalManagementController;
use App\Http\Controllers\DMS\Repo\RepositoryController;
use App\Http\Controllers\DMS\Repo\RepositoryPermissionController;
use App\Http\Controllers\DMS\SearchManagementController;
use App\Http\Controllers\DMS\Tags\TagController;
use App\Http\Controllers\DMS\TrailManagementController;
use App\Http\Controllers\DMS\UploadManagementController;
use App\Http\Controllers\DMS\VersioncontrolManagementController;
use Illuminate\Support\Facades\Route;


Route::namespace('DMS')->prefix('dms')->group(function () {

    Route::get('recent', DocumentRecentController::class)->name('repo.recent');
    Route::get('bulk-upload', DocumentUploadController::class)->name('files.upload');
    Route::prefix('document/{document}')->group(function () {
        Route::get('activities', DocumentActivityController::class)->name('file.activities');
        Route::get('preview', [DocumentActionsConroller::class, 'preview'])->name('file.preview');
        Route::put('file-visibility', [DocumentPermissionController::class, 'visibility'])->name('file.visibility');
        Route::resource('file-permissions', DocumentPermissionController::class)->only(['index', 'store', 'destroy']);
    });

    Route::prefix('repo/{repository}')->group(function () {
        Route::put('repo-visibility', [RepositoryPermissionController::class, 'visibility'])->name('repo.visibility');
        Route::resource('repo-permissions', RepositoryPermissionController::class)->only(['index', 'store', 'destroy']);

        Route::resource('files', DocumentController::class)->parameters(['files' => 'document'])->except('create');
    });
    Route::resource('repo', RepositoryController::class)->parameters(['repo' => 'repository']);
    Route::resource('file-tags', TagController::class)->parameters(['file-tags' => 'DMSTag']);


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
