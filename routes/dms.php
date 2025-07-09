<?php

use App\Http\Controllers\DMS\DocumentRecentController;
use App\Http\Controllers\DMS\Files\DocumentActionsController;
use App\Http\Controllers\DMS\Files\DocumentActivityController;
use App\Http\Controllers\DMS\Files\DocumentController;
use App\Http\Controllers\DMS\Files\DocumentPermissionController;
use App\Http\Controllers\DMS\Files\DocumentUploadController;
use App\Http\Controllers\DMS\Repo\RepositoryController;
use App\Http\Controllers\DMS\Repo\RepositoryPermissionController;
use App\Http\Controllers\DMS\Tags\DocumentTagController;
use App\Http\Controllers\DMS\Tags\TagController;
use App\Http\Controllers\DMS\Tags\TaggingRuleController;
use Illuminate\Support\Facades\Route;

Route::namespace('DMS')->prefix('dms')->group(function () {
    Route::get('recent', DocumentRecentController::class)->name('repo.recent');
    Route::get('bulk-upload', DocumentUploadController::class)->name('files.upload');
    Route::prefix('document/{document}')->group(function () {
        Route::get('activities', DocumentActivityController::class)->name('file.activities');
        Route::get('preview', [DocumentActionsController::class, 'preview'])->name('file.preview');
        Route::put('file-visibility', [DocumentPermissionController::class, 'visibility'])->name('file.visibility');
        Route::resource('file-permissions', DocumentPermissionController::class)->only(['index', 'store', 'destroy']);
    });

    Route::prefix('repo/{repository}')->group(function () {
        Route::put('repo-visibility', [RepositoryPermissionController::class, 'visibility'])->name('repo.visibility');
        Route::resource('repo-permissions', RepositoryPermissionController::class)->only(['index', 'store', 'destroy']);

        Route::resource('files', DocumentController::class)->parameters(['files' => 'document'])->except('create');
    });
    Route::resource('repo', RepositoryController::class)->parameters(['repo' => 'repository']);

    Route::prefix('file-tags/{d_m_s_tags}')->group(function () {
        Route::get('files', DocumentTagController::class)->name('file-tags.files');
        Route::resource('tagging-rules', TaggingRuleController::class)
            ->parameters(['tagging-rules' => 'document_tagging_rules'])->except(['show', 'edit', 'update']);

    });
    Route::resource('file-tags', TagController::class)->parameters(['file-tags' => 'd_m_s_tags'])->except('edit');
});
