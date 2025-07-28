<?php

use App\Http\Controllers\Web\ThirdParty\ThirdPartyWebController;
use Illuminate\Support\Facades\Route;

Route::resource('web/parties', ThirdPartyWebController::class)->only([
    'index',
    'show',
    'edit',
    'store',
    'create',
    'update',
    'destroy'
])->names([
    'index' => 'web.parties.index',
    'show' => 'web.parties.show',
    'create' => 'web.parties.create',
    'store' => 'web.parties.store',
    'edit' => 'web.parties.edit',
    'update' => 'web.parties.update',
    'destroy' => 'web.parties.destroy',
]);
