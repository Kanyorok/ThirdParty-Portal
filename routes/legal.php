<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Legal\LegalDocumentController;
use App\Http\Controllers\Legal\LegalDispatchController;
use App\Http\Controllers\Legal\LegalContractController;
use App\Http\Controllers\Legal\LegalClauseController;
use App\Http\Controllers\Legal\ContractObligationController;
use App\Http\Controllers\Legal\LegalTemplateController;
use App\Http\Controllers\Legal\LegalCaseController;
use App\Http\Controllers\Legal\LegalCaseEvidenceController;
use App\Http\Controllers\Legal\LegalCounselController;
use App\Http\Controllers\Legal\LegalCaseOutcomeController;
use App\Http\Controllers\Legal\LegalObligationController;
use App\Http\Controllers\Legal\LegalObligationAssignmentController;
use App\Http\Controllers\Legal\LegalSearchRequestController;
use App\Http\Controllers\Legal\LoanSecurityController;
use App\Http\Controllers\Legal\LegalIntellectualPropertyController;
use App\Http\Controllers\Legal\LegalIPTrackingController;



Route::namespace('Legal')->prefix('legal')->group(function () {
Route::prefix('legal')->name('legal.')->group(function () {
    Route::resource('documents', LegalDocumentController::class);

    // ✅ Nested resource: documents.dispatches
    Route::resource('documents.dispatches', LegalDispatchController::class);
});  

Route::prefix('legal')->name('legal.')->group(function () {
    Route::resource('documents', LegalDocumentController::class);
    Route::resource('documents.dispatches', LegalDispatchController::class);
    Route::resource('documents.execution_logs', LegalExecutionLogController::class); // ✅ new
});

Route::prefix('legal/contracts')->name('legal.contracts.')->group(function () {
    Route::get('/', [LegalContractController::class, 'index'])->name('index');
    Route::get('/{id}', [LegalContractController::class, 'show'])->name('show'); // optional
    Route::get('legal/contracts/{id}/review', [LegalContractController::class, 'review'])->name('legal.contracts.review');
    Route::post('legal/contracts/{id}/review', [LegalContractController::class, 'submitReview'])->name('legal.contracts.submitReview');

});


Route::prefix('legal/clauses')->name('legal.clauses.')->group(function () {
    Route::get('/', [LegalClauseController::class, 'index'])->name('index');
    Route::get('/create', [LegalClauseController::class, 'create'])->name('create');
    Route::post('/store', [LegalClauseController::class, 'store'])->name('store');
    Route::get('/{id}', [LegalClauseController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [LegalClauseController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalClauseController::class, 'update'])->name('update');
    Route::delete('/{id}', [LegalClauseController::class, 'destroy'])->name('destroy');
});

Route::prefix('legal/documents')->name('legal.documents.')->group(function () {
    // Obligation Routes (Nested under a LegalDocument)
    Route::get('{document}/obligations', [ContractObligationController::class, 'index'])->name('obligations.index');
    Route::get('{document}/obligations/create', [ContractObligationController::class, 'create'])->name('obligations.create');
    Route::post('{document}/obligations', [ContractObligationController::class, 'store'])->name('obligations.store');

    // Edit/Update Obligation
    Route::get('obligations/{id}/edit', [ContractObligationController::class, 'edit'])->name('obligations.edit');
    Route::put('obligations/{id}', [ContractObligationController::class, 'update'])->name('obligations.update');

    // Archive/Delete
    Route::delete('obligations/{id}', [ContractObligationController::class, 'destroy'])->name('obligations.destroy');
});


Route::prefix('legal/templates')->name('legal.templates.')->group(function () {
    Route::get('/', [LegalTemplateController::class, 'index'])->name('index');
    Route::get('/create', [LegalTemplateController::class, 'create'])->name('create');
    Route::post('/', [LegalTemplateController::class, 'store'])->name('store');
    Route::get('/{id}', [LegalTemplateController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [LegalTemplateController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalTemplateController::class, 'update'])->name('update');
    Route::delete('/{id}', [LegalTemplateController::class, 'destroy'])->name('destroy');
});

Route::prefix('legal/drafts')->name('legal.drafts.')->group(function () {
    Route::get('/clauses/list', [LegalDraftController::class, 'fetchClauses'])->name('clauses'); // 🟢 Move this above
    Route::get('/', [LegalDraftController::class, 'index'])->name('index');
    Route::get('/create', [LegalDraftController::class, 'create'])->name('create');
    Route::post('/', [LegalDraftController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LegalDraftController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalDraftController::class, 'update'])->name('update');
    Route::get('/{id}', [LegalDraftController::class, 'show'])->name('show');
});
Route::prefix('legal/cases')->name('legal.cases.')->group(function () {
    Route::get('/', [LegalCaseController::class, 'index'])->name('index');
    Route::get('/create', [LegalCaseController::class, 'create'])->name('create');
    Route::post('/', [LegalCaseController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LegalCaseController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalCaseController::class, 'update'])->name('update');
    Route::get('/{id}', [LegalCaseController::class, 'show'])->name('show');
});
Route::prefix('legal/cases/{case}/evidence')->name('legal.cases.evidence.')->group(function () {
    Route::get('/', [LegalCaseEvidenceController::class, 'index'])->name('index');
    Route::get('/create', [LegalCaseEvidenceController::class, 'create'])->name('create');
    Route::post('/', [LegalCaseEvidenceController::class, 'store'])->name('store');
});

Route::prefix('legal/disputes/counsels')->name('legal.disputes.counsels.')->group(function () {
    Route::get('/', [LegalCounselController::class, 'index'])->name('index');
    Route::get('/create', [LegalCounselController::class, 'create'])->name('create');
    Route::post('/', [LegalCounselController::class, 'store'])->name('store');
    Route::get('/{id}', [LegalCounselController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [LegalCounselController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalCounselController::class, 'update'])->name('update');
    Route::delete('/{id}', [LegalCounselController::class, 'destroy'])->name('destroy');
});
Route::prefix('legal/disputes/outcomes')->name('legal.disputes.outcomes.')->group(function () {
    Route::get('/', [LegalCaseOutcomeController::class, 'index'])->name('index');
    Route::get('/create', [LegalCaseOutcomeController::class, 'create'])->name('create');
    Route::post('/', [LegalCaseOutcomeController::class, 'store'])->name('store');
    Route::get('/{id}', [LegalCaseOutcomeController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [LegalCaseOutcomeController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalCaseOutcomeController::class, 'update'])->name('update');
    Route::delete('/{id}', [LegalCaseOutcomeController::class, 'destroy'])->name('destroy');
});


Route::prefix('legal/obligations')->name('legal.obligations.')->group(function () {
    Route::get('/', [LegalObligationController::class, 'index'])->name('index');
    Route::get('/create', [LegalObligationController::class, 'create'])->name('create');
    Route::post('/', [LegalObligationController::class, 'store'])->name('store');
    Route::get('/calendar', [LegalObligationController::class, 'calendar'])->name('calendar');
    Route::get('/{id}/edit', [LegalObligationController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalObligationController::class, 'update'])->name('update');
    Route::patch('/{id}/status', [LegalObligationController::class, 'updateStatus'])->name('update_status');

    Route::prefix('{id}/assignments')->name('assignments.')->group(function () {
        Route::get('/', [LegalObligationAssignmentController::class, 'index'])->name('index');
        Route::get('/create', [LegalObligationAssignmentController::class, 'create'])->name('create');
        Route::post('/', [LegalObligationAssignmentController::class, 'store'])->name('store');
    });
});
Route::prefix('legal/search-requests')->name('legal.search_requests.')->group(function () {
    Route::get('/', [LegalSearchRequestController::class, 'index'])->name('index');
    Route::get('/create', [LegalSearchRequestController::class, 'create'])->name('create');
    Route::post('/', [LegalSearchRequestController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LegalSearchRequestController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalSearchRequestController::class, 'update'])->name('update');
    Route::get('/{id}', [LegalSearchRequestController::class, 'show'])->name('show');
});
Route::prefix('legal/securities')->name('legal.securities.')->group(function () {
    Route::get('/', [LoanSecurityController::class, 'index'])->name('index');
    Route::get('/create', [LoanSecurityController::class, 'create'])->name('create');
    Route::post('/', [LoanSecurityController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LoanSecurityController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LoanSecurityController::class, 'update'])->name('update');
});
Route::prefix('legal/intellectual')->name('legal.intellectual.')->group(function () {
    Route::get('/', [LegalIntellectualPropertyController::class, 'index'])->name('index');
    Route::get('/create', [LegalIntellectualPropertyController::class, 'create'])->name('create');
    Route::post('/', [LegalIntellectualPropertyController::class, 'store'])->name('store');
    Route::get('/{id}', [LegalIntellectualPropertyController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [LegalIntellectualPropertyController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalIntellectualPropertyController::class, 'update'])->name('update');
    Route::delete('/{id}', [LegalIntellectualPropertyController::class, 'destroy'])->name('destroy');
});

Route::prefix('legal/ip-tracking')->name('legal.ip_tracking.')->group(function () {
    Route::get('/', [LegalIPTrackingController::class, 'index'])->name('index');
    Route::get('/create', [LegalIPTrackingController::class, 'create'])->name('create');
    Route::post('/', [LegalIPTrackingController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LegalIPTrackingController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LegalIPTrackingController::class, 'update'])->name('update');
});
});

