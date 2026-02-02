<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->namespace('App\Http\Controllers\Settings')
    ->prefix('settings')
    ->group(function () {
        Route::get('user-sessions', 'UserSessionController@index')
            ->middleware(\App\Http\Middleware\CanAction::class . ':read,user-sessions')
            ->name('settings.user-sessions.index');
        Route::post('user-sessions/revoke/{id}', 'UserSessionController@revoke')
            ->middleware(\App\Http\Middleware\CanAction::class . ':update,user-sessions')
            ->name('settings.user-sessions.revoke');
        Route::post('user-sessions/revoke-others/{userId}', 'UserSessionController@revokeOthers')
            ->middleware(\App\Http\Middleware\CanAction::class . ':approve,user-sessions')
            ->name('settings.user-sessions.revoke-others');
    });
