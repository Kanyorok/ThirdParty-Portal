<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationRoundController;

Route::prefix('prequalification')
    ->name('prequalification.')
    ->group(function () {
        Route::get('prequalification-rounds', [PrequalificationRoundController::class, 'index'])->name('prequalification-rounds.index');
        Route::get('prequalification-rounds/create', [PrequalificationRoundController::class, 'create'])->name('prequalification-rounds.create');
        Route::post('prequalification-rounds', [PrequalificationRoundController::class, 'store'])->name('prequalification-rounds.store');
        Route::get('prequalification-rounds/{prequalification_round}', [PrequalificationRoundController::class, 'show'])->name('prequalification-rounds.show');
        Route::get('prequalification-rounds/{prequalification_round}/edit', [PrequalificationRoundController::class, 'edit'])->name('prequalification-rounds.edit');
        Route::put('prequalification-rounds/{prequalification_round}', [PrequalificationRoundController::class, 'update'])->name('prequalification-rounds.update');
        Route::delete('prequalification-rounds/{prequalification_round}', [PrequalificationRoundController::class, 'destroy'])->name('prequalification-rounds.destroy');
    });


Route::get('/sections/{section}/criteria', [\App\Http\Controllers\Procurement\CriteriaController::class, 'fetchForSection'])
    ->name('sections.criteria.fetch');

Route::get('procurement/criteria/section/{id}', [CriteriaController::class, 'getBySection']);
