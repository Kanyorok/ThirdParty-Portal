<?php

use Illuminate\Support\Facades\Route;

Route::namespace('ThirdParty')->prefix('thirdparty')->name('thirdparty.')->group(function () {
    Route::post('parties/bulk-action', 'ThirdPartyWebController@bulkAction')->name('parties.bulk-action');
    Route::get('parties/search-existing', 'ThirdPartyController@searchExisting')->name('parties.search-existing');
    Route::post('parties/add-role', 'ThirdPartyController@addRole')->name('parties.add-role');


    Route::prefix('parties/{parties}')->name('parties.')->group(function () {
        Route::resource('banks', 'ThirdPartiesBankController');
    });
    Route::resource('parties', 'ThirdPartyController');
});
