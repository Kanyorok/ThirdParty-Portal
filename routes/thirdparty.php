<?php

use App\Http\Controllers\Web\ThirdParty\ThirdPartyWebController;
use Illuminate\Support\Facades\Route;

Route::prefix('thirdparty')->name('thirdparty.')->group(function () {
    Route::resource('parties', ThirdPartyWebController::class)
        ->only([
            'index',
            'show',
            'edit',
            'store',
            'create',
            'update',
            'destroy'
        ]);

    // Bulk actions route
    Route::post('parties/bulk-action', [ThirdPartyWebController::class, 'bulkAction'])->name('parties.bulk-action');
});
