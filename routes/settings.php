<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->namespace('App\Http\Controllers\Settings')
    ->prefix('settings')
    ->group(function () {
        Route::get('user-sessions', 'UserSessionController@index')->name('settings.user-sessions.index');
        Route::post('user-sessions/revoke/{id}', 'UserSessionController@revoke')->name('settings.user-sessions.revoke');
        Route::post('user-sessions/revoke-others/{userId}', 'UserSessionController@revokeOthers')->name('settings.user-sessions.revoke-others');
    });


