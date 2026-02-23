<?php

use App\Http\Controllers\DMS\Files\DocumentActionsController;
use App\Http\Controllers\DMS\Files\DocumentActivityController;
use App\Http\Controllers\DMS\Files\DocumentCheckOutController;
use App\Http\Controllers\DMS\Files\DocumentController;
use App\Http\Controllers\DMS\Files\DocumentDownloadController;
use App\Http\Controllers\DMS\Files\DocumentLegalHoldController as FileLegalHoldController;
use App\Http\Controllers\DMS\Files\DocumentMoveController;
use App\Http\Controllers\DMS\Files\DocumentPermissionController;
use App\Http\Controllers\DMS\Files\DocumentPreviewController;
use App\Http\Controllers\DMS\Files\DocumentRecentController;
use App\Http\Controllers\DMS\Files\DocumentTagsController;
use App\Http\Controllers\DMS\Files\DocumentUploadController;
use App\Http\Controllers\DMS\Files\DocumentValidationController as FileValidationController;
use App\Http\Controllers\DMS\Files\TrashDocumentController;
use App\Http\Controllers\DMS\LegalHold\DocumentLegalHoldController;
use App\Http\Controllers\DMS\LegalHold\LegalHoldController;
use App\Http\Controllers\DMS\Repo\RepositoryController;
use App\Http\Controllers\DMS\Repo\RepositoryMoveController;
use App\Http\Controllers\DMS\Repo\RepositoryPermissionController;
use App\Http\Controllers\DMS\ReportsController;
use App\Http\Controllers\DMS\SearchController;
use App\Http\Controllers\DMS\Tags\DocumentTagController;
use App\Http\Controllers\DMS\Tags\TagController;
use App\Http\Controllers\DMS\Tags\TaggingRuleController;
use App\Http\Controllers\DMS\Verification\DocumentValidationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['module:700000'])->namespace('DMS')->prefix('dms')->group(function () {
    Route::get('search', SearchController::class)->name('dms.search');
    Route::get('recent', DocumentRecentController::class)->name('repo.recent');
    Route::get('bulk-upload', DocumentUploadController::class)->name('files.upload');
    Route::prefix('document/{document}')->group(function () {
        //mark for validation -> ()
        Route::get('legal-holds', FileLegalHoldController::class)->name('file.legal-holds');
        Route::get('activities', DocumentActivityController::class)->name('file.activities');
        Route::get('embed-preview', DocumentPreviewController::class)->name('file.embed-preview');
        Route::get('preview', [DocumentActionsController::class, 'preview'])->name('file.preview');
        Route::put('file-visibility', [DocumentPermissionController::class, 'visibility'])->name('file.visibility');
        Route::resource('file-validation', FileValidationController::class)->only(['index', 'store']);
        Route::resource('file-move', DocumentMoveController::class)->only(['index', 'store']);
        Route::resource('file-download', DocumentDownloadController::class)->only(['index', 'store']);
        Route::resource('document-checkouts', DocumentCheckOutController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('document-tags', DocumentTagsController::class)->only(['create', 'store']);
        Route::resource('file-permissions', DocumentPermissionController::class)->only(['index', 'store', 'destroy']);
    });

    Route::prefix('repo/{repository}')->group(function () {
        Route::put('repo-visibility', [RepositoryPermissionController::class, 'visibility'])->name('repo.visibility');

        Route::resource('repo-permissions', RepositoryPermissionController::class)->only(['index', 'store', 'destroy']);
        Route::resource('repo-move', RepositoryMoveController::class)->only(['index', 'store']);
        Route::resource('files', DocumentController::class)->parameters(['files' => 'document'])->except('create');
    });
    Route::resource('repo', RepositoryController::class)->parameters(['repo' => 'repository'])->except('create');

    Route::put('validation/{documentValidation}/approve', [DocumentValidationController::class, 'approve'])->name('dms.validation.approve');
    Route::put('validation/{documentValidation}/reject', [DocumentValidationController::class, 'reject'])->name('dms.validation.reject');
    Route::resource('validation', DocumentValidationController::class)->names([
        'index' => 'dms.validation.index',
        'show' => 'dms.validation.show',
        'update' => 'dms.validation.update',
        'destroy' => 'dms.validation.destroy',
    ])->only(['index', 'show', 'update', 'destroy']);

    Route::prefix('file-tags/{d_m_s_tags}')->group(function () {
        Route::get('files', DocumentTagController::class)->name('file-tags.files');
        Route::resource('tagging-rules', TaggingRuleController::class)
            ->parameters(['tagging-rules' => 'document_tagging_rules'])->except(['show', 'edit', 'update']);
    });
    Route::resource('file-tags', TagController::class)->parameters(['file-tags' => 'd_m_s_tags'])->except('edit');

    Route::prefix('legal-hold/{d_m_s_legal_hold}')->namespace('LegalHold')->group(function () {
        Route::get('activities', 'LegalHoldActivityController')->name('hold-files.activities');
        Route::resource('hold-files', DocumentLegalHoldController::class)->parameters(['hold-files' => 'document'])->except('edit');
        Route::post('release', [LegalHoldController::class, 'release'])->name('legal-hold.release');
    });
    Route::resource('legal-hold', LegalHoldController::class)->parameters(['legal-hold' => 'dMSLegalHold'])->except('edit');

    Route::resource('document-trashed', TrashDocumentController::class)->parameters(['document-trashed' => 'document'])
        ->names([
            'update' => 'document-trashed.restore',
        ])->only(['index', 'store', 'update', 'destroy']);

    //settings
    Route::get('document-signature/{dMSSignature}/documents', \App\Http\Controllers\DMS\Settings\SignatureDocumentsController::class)->name('document-signature.documents');
    Route::resource('document-signature', \App\Http\Controllers\DMS\Settings\SignatureController::class)->parameters(['document-signature' => 'dMSSignature']);//->except('edit');

    Route::resource('document-validation-type/{documentValidationType}/doc-validation-type-approvers', \App\Http\Controllers\DMS\Settings\ValidationTypeApproverController::class)->only(['index', 'store', 'destroy']);
    Route::resource('document-validation-type', \App\Http\Controllers\DMS\Settings\DocumentValidationTypeController::class)->parameters(['document-signature' => 'documentValidationType'])->except(['create', 'edit']);

    //reports
    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('dms-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'dms-reports.index',
        'show' => 'dms-reports.show',
    ]);
});
