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
use App\Http\Controllers\Legal\RegulatoryTaskController;
use App\Http\Controllers\Legal\RegulatoryObligationController;
use App\Http\Controllers\Legal\ComplianceCalendarController;
use App\Http\Controllers\Legal\ComplianceIncidentController;

use Illuminate\Support\Facades\Route;


Route::middleware(['module:800000'])->prefix('legal')->group(function () {
    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('legal-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'legal-reports.index',
        'show' => 'legal-reports.show'
    ]);
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
        Route::get('obligations/{id}', [LegalObligationController::class, 'getObligations'])->name('obligations.getObligations');
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


});

Route::prefix('legal/compliance/obligations/{obligationId}/tasks')->name('legal.compliance.tasks.')->group(function () {
    Route::get('/', [RegulatoryTaskController::class, 'index'])->name('index');
    Route::get('/create', [RegulatoryTaskController::class, 'create'])->name('create');
    Route::post('/', [RegulatoryTaskController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [RegulatoryTaskController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RegulatoryTaskController::class, 'update'])->name('update');
});

Route::prefix('legal/compliance/obligations/{obligationId}/tasks')->name('legal.compliance.tasks.')->group(function () {
    Route::get('/', [RegulatoryTaskController::class, 'index'])->name('index');
    Route::get('/create', [RegulatoryTaskController::class, 'create'])->name('create');
    Route::post('/', [RegulatoryTaskController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [RegulatoryTaskController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RegulatoryTaskController::class, 'update'])->name('update');
});

Route::prefix('legal/compliance/obligations')->name('legal.compliance.obligations.')->group(function () {
    Route::get('/', [RegulatoryObligationController::class, 'index'])->name('index');
    Route::get('/create', [RegulatoryObligationController::class, 'create'])->name('create');
    Route::post('/', [RegulatoryObligationController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [RegulatoryObligationController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RegulatoryObligationController::class, 'update'])->name('update');
});

Route::prefix('legal/compliance/calendar')->name('legal.compliance.calendar.')->group(function () {
    Route::get('/', [ComplianceCalendarController::class, 'index'])->name('index');
    Route::get('/create', [ComplianceCalendarController::class, 'create'])->name('create');
    Route::post('/', [ComplianceCalendarController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ComplianceCalendarController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ComplianceCalendarController::class, 'update'])->name('update');
    Route::delete('/{id}', [ComplianceCalendarController::class, 'destroy'])->name('destroy');
});

Route::prefix('legal/setup')->name('legal.setup.')->group(function () {
    Route::resource('regulatory_bodies', \App\Http\Controllers\Legal\Setup\RegulatoryBodyController::class);
    Route::resource('compliance_areas', \App\Http\Controllers\Legal\Setup\ComplianceAreaController::class);
    Route::resource('control_types', \App\Http\Controllers\Legal\Setup\ControlTypeController::class);
    Route::resource('incident_severity_levels', \App\Http\Controllers\Legal\Setup\IncidentSeverityLevelController::class);
    Route::resource('filing_types', \App\Http\Controllers\Legal\Setup\FilingTypeController::class);
    Route::resource('file_formats', \App\Http\Controllers\Legal\Setup\FileFormatController::class);
    Route::resource('policy_categories', \App\Http\Controllers\Legal\Setup\PolicyCategoryController::class);
    Route::resource('training_types', \App\Http\Controllers\Legal\Setup\TrainingTypeController::class);
});

Route::prefix('legal/compliance')->name('legal.compliance.')->group(function () {
    Route::resource('obligations', \App\Http\Controllers\Legal\ComplianceObligationController::class);
    Route::post('obligations/{id}/upload-doc', [\App\Http\Controllers\Legal\ComplianceObligationController::class, 'uploadDoc'])->name('obligations.uploadDoc');
    Route::post('obligations/{id}/add-impact', [\App\Http\Controllers\Legal\ComplianceObligationController::class, 'addImpact'])->name('obligations.addImpact');
});

Route::prefix('legal/compliance')->name('legal.compliance.')->group(function () {
    Route::resource('calendar', \App\Http\Controllers\Legal\ComplianceCalendarController::class);
    Route::post('calendar/{id}/add-alert', [\App\Http\Controllers\Legal\ComplianceCalendarController::class, 'addAlert'])->name('calendar.addAlert');
});

Route::prefix('legal/compliance')->name('legal.compliance.')->group(function () {
    Route::resource('controls', \App\Http\Controllers\Legal\ComplianceControlController::class);
    Route::post('controls/{id}/upload-evidence', [\App\Http\Controllers\Legal\ComplianceControlController::class, 'uploadEvidence'])->name('controls.uploadEvidence');
});

Route::prefix('legal/compliance')->name('legal.compliance.')->group(function () {
    Route::resource('incidents', \App\Http\Controllers\Legal\ComplianceIncidentController::class);
    Route::post('incidents/{id}/add-action', [\App\Http\Controllers\Legal\ComplianceIncidentController::class, 'addAction'])->name('incidents.addAction');
    Route::get('incidents-dashboard', [\App\Http\Controllers\Legal\ComplianceIncidentController::class, 'dashboard'])
        ->name('incidents.dashboard');
});

Route::prefix('legal/compliance')->name('legal.compliance.')->group(function () {
    Route::get('filings/templates', [\App\Http\Controllers\Legal\ComplianceFilingController::class, 'templates'])->name('filings.templates');
    Route::get('filings/templates/create', [\App\Http\Controllers\Legal\ComplianceFilingController::class, 'createTemplate'])->name('filings.templates.create');
    Route::post('filings/templates/store', [\App\Http\Controllers\Legal\ComplianceFilingController::class, 'storeTemplate'])->name('filings.templates.store');

    Route::resource('filings', \App\Http\Controllers\Legal\ComplianceFilingController::class);
    Route::post('filings/{id}/upload-ack', [\App\Http\Controllers\Legal\ComplianceFilingController::class, 'uploadAck'])->name('filings.uploadAck');
});
