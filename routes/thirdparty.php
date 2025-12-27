<?php

use Illuminate\Support\Facades\Route;

Route::namespace('ThirdParty')->prefix('thirdparty')->name('thirdparty.')->group(function () {
    Route::post('parties/bulk-action', 'ThirdPartyWebController@bulkAction')->name('parties.bulk-action');


    Route::prefix('parties/{parties}')->name('parties.')->group(function () {
        Route::resource('banks', 'ThirdPartiesBankController');
    });
    Route::resource('parties', 'ThirdPartyController');
});
