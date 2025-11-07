<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->namespace('App\Http\Controllers\Settings')
    ->prefix('settings')
    ->group(function () {
        Route::get('user-sessions', 'UserSessionController@index')
            ->middleware('canAction:read,user-sessions')
            ->name('settings.user-sessions.index');
        Route::post('user-sessions/revoke/{id}', 'UserSessionController@revoke')
            ->middleware('canAction:update,user-sessions')
            ->name('settings.user-sessions.revoke');
        Route::post('user-sessions/revoke-others/{userId}', 'UserSessionController@revokeOthers')
            ->middleware('canAction:approve,user-sessions')
            ->name('settings.user-sessions.revoke-others');
    });


