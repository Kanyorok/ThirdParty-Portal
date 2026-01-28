<?php

use App\Http\Controllers\Procurement\CriteriaController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationEvaluationController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationResultsController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationRoundController;
use App\Http\Controllers\Procurement\SectionController;
use Illuminate\Support\Facades\Route;

// Add this line

Route::prefix('prequalification')
    ->name('prequalification.')
    ->group(function () {
        Route::resource('prequalification-rounds', PrequalificationRoundController::class);
        Route::resource('sections', SectionController::class);
        Route::prefix('sections/{section}')->name('sections.')->group(function () {
            Route::resource('criteria', CriteriaController::class)
                ->only(['index', 'store', 'update', 'destroy', 'show', 'edit']);
        });
        Route::get('sections/{section}/criteria', [CriteriaController::class, 'fetchAll'])
            ->name('sections.criteria.fetch');

        Route::resource('applications', PrequalificationApplicationController::class)
            ->except(['create', 'store', 'evaluate']);

        // Evaluation Routes (for form display and submission)
        Route::controller(PrequalificationEvaluationController::class)->group(function () {
            Route::get('applications/{applicationId}/evaluate', 'showEvaluationForm')
                ->name('prequalification-evaluation.show');
            Route::post('applications/{applicationId}/evaluate', 'submitEvaluation')
                ->name('prequalification-evaluation.submit');
            Route::post('rounds/{roundId}/prequalify/bulk', 'bulkPrequalify')
                ->name('prequalification-evaluation.prequalify.bulk');
            Route::post('rounds/{roundId}/prequalify/{thirdPartyId}/{categoryId}', 'prequalifySupplier')
                ->name('prequalification-evaluation.prequalify.single');
            Route::post('rounds/expire/run', 'expireRounds')
                ->name('prequalification-evaluation.rounds.expire');
            Route::get('evaluations/datatable', 'datatable')
                ->name('prequalification-evaluation.datatable');
        });

        // Results Routes (for viewing)
        Route::controller(PrequalificationResultsController::class)->group(function () {
            Route::get('applications/{applicationId}/results', 'showResults')
                ->name('prequalification-evaluation.results');
        });
    });
