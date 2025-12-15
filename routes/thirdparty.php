<?php

use App\Http\Controllers\ThirdParty\EmailVerificationController;
use App\Http\Controllers\ThirdParty\PasswordController;
use App\Http\Controllers\ThirdParty\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThirdParty\ThirdPartyAuthController;

Route::prefix('v1/portal')->name('portal.')->group(function () {

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [ThirdPartyAuthController::class, 'register'])->name('register');
        Route::post('login', [ThirdPartyAuthController::class, 'login'])->name('login');

        Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware('signed')
            ->name('verification.verify');
        Route::post('email/resend', [EmailVerificationController::class, 'resend'])
            ->name('verification.resend');
    });

    Route::middleware('auth:thirdparty')->group(function () {
        Route::post('auth/logout', [ThirdPartyAuthController::class, 'logout'])->name('auth.logout');
        Route::post('auth/logout-all', [ThirdPartyAuthController::class, 'logoutAll'])->name('auth.logout-all');
        Route::get('auth/me', [ThirdPartyAuthController::class, 'me'])->name('auth.me');

        Route::put('password', [PasswordController::class, 'update'])->name('password.update');

        Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('profile', [ProfileController::class, 'store'])->name('profile.store');
        Route::get('profile/status', [ProfileController::class, 'status'])->name('profile.status');
    });
});
