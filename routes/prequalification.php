<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationRoundController;
use App\Http\Controllers\Procurement\CriteriaController;
use App\Http\Controllers\Procurement\SectionController;

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
    });
