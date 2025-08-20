<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationRoundController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;
use App\Http\Controllers\Procurement\CriteriaController;
use App\Http\Controllers\Procurement\SectionController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationEvaluationController;

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

        // Evaluation Routes
        Route::get('applications/{applicationId}/evaluate', [PrequalificationEvaluationController::class, 'showEvaluationForm'])
            ->name('prequalification-evaluation.show');
        Route::post('applications/{applicationId}/evaluate', [PrequalificationEvaluationController::class, 'submitEvaluation'])
            ->name('prequalification-evaluation.submit');
        Route::post('applications/{applicationId}/generate-results', [PrequalificationEvaluationController::class, 'generateResults'])
            ->name('prequalification-evaluation.generate-results');
        Route::get('applications/{applicationId}/results', [PrequalificationEvaluationController::class, 'showResults'])
            ->name('prequalification-evaluation.results');
    });
