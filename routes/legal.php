<?php

use App\Http\Controllers\Legal\ContractObligationController;
use App\Http\Controllers\Legal\LegalCaseController;
use App\Http\Controllers\Legal\LegalCaseEvidenceController;
use App\Http\Controllers\Legal\LegalCaseOutcomeController;
use App\Http\Controllers\Legal\LegalClauseController;
use App\Http\Controllers\Legal\LegalContractController;
use App\Http\Controllers\Legal\LegalCounselController;
use App\Http\Controllers\Legal\LegalDispatchController;
use App\Http\Controllers\Legal\LegalDocumentController;
use App\Http\Controllers\Legal\LegalDraftController;
use App\Http\Controllers\Legal\LegalExecutionLogController;
use App\Http\Controllers\Legal\LegalIntellectualPropertyController;
use App\Http\Controllers\Legal\LegalIPTrackingController;
use App\Http\Controllers\Legal\LegalObligationAssignmentController;
use App\Http\Controllers\Legal\LegalObligationController;
use App\Http\Controllers\Legal\LegalSearchRequestController;
use App\Http\Controllers\Legal\LegalTemplateController;
use App\Http\Controllers\Legal\LoanSecurityController;
use App\Http\Controllers\Legal\ReportsController;
use Illuminate\Support\Facades\Route;

Route::prefix('legal')->group(function () {
    Route::name('legal.')->group(function () {

    // Legal Documents & nested dispatches/execution logs
    Route::resource('documents', LegalDocumentController::class);
    Route::resource('documents.dispatches', LegalDispatchController::class);
    Route::resource('documents.execution_logs', LegalExecutionLogController::class);

    // Contracts
    Route::resource('maintenance', LegalContractController::class);

    // Clauses
    Route::resource('clauses', LegalClauseController::class);

    // Obligations (nested under documents)
    Route::resource('documents.obligations', ContractObligationController::class);

    // Templates
    Route::resource('templates', LegalTemplateController::class);

    // Drafts
    Route::resource('drafts', LegalDraftController::class);

    // Cases
    Route::resource('cases', LegalCaseController::class);

    // Case Evidence (nested under cases)
    Route::resource('cases.evidence', LegalCaseEvidenceController::class);

    // Counsels
    Route::resource('disputes.counsels', LegalCounselController::class);

    // Outcomes
    Route::resource('disputes.outcomes', LegalCaseOutcomeController::class);

    // Obligations (main list)
    Route::resource('obligations', LegalObligationController::class);
    Route::get('/legal/obligations/{id}', [LegalObligationController::class,'getObligations'])->name('legal.getObligations');
    Route::patch('obligations/{id}/assignUser', [LegalObligationController::class, 'assignUser'])
    ->name('obligations.assignUser');

    // Obligation Assignments (nested under obligations)
    Route::resource('obligations.assignments', LegalObligationAssignmentController::class);

    // Search Requests
    Route::resource('search_requests', LegalSearchRequestController::class);
    Route::patch('store_findings/{id}', [LegalSearchRequestController::class, 'storeApprovalStatus'])->name('store_findings.storeApprovalStatus');


    // Securities
    Route::resource('securities', LoanSecurityController::class);

    // Intellectual Property
    Route::resource('intellectual', LegalIntellectualPropertyController::class);
    Route::patch('raiseDispute/{id}', [LegalIntellectualPropertyController::class, 'raiseDispute'])->name('intellectual.raiseDispute');

    // IP Tracking
    Route::resource('ip-tracking', LegalIPTrackingController::class);
    });
    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('legal-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'legal-reports.index',
        'show' => 'legal-reports.show'
    ]);
});
