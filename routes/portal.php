<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThirdParty\ThirdPartyAuthController;
use App\Http\Controllers\ThirdParty\SupplierRegistrationController;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use App\Http\Controllers\ThirdParty\MetadataController;
use App\Http\Controllers\ThirdParty\ThirdPartyProfileController;

Route::prefix('portal/auth')->name('portal.auth.')->group(function () {

    // Public Routes
    Route::post('register', SupplierRegistrationController::class)
        ->name('register')
        ->middleware(['throttle:5,1']);

    Route::post('login', [ThirdPartyAuthController::class, 'login'])
        ->name('login')
        ->middleware(['throttle:10,1']);

    Route::get('metadata/countries', [MetadataController::class, 'getCountries']);
    Route::get('metadata/business-types', [MetadataController::class, 'getBusinessTypes']);
    Route::get('metadata/supplier-categories', [MetadataController::class, 'getSupplierCategories']);

    Route::get('email/verify/{id}/{hash}', [ThirdPartyAuthController::class, 'verify'])
        ->name('verification.verify')
        ->middleware(['signed', 'throttle:6,1']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {

        // 1. Profile Completion (Allow before email verification)
        Route::post('complete-profile', [SupplierRegistrationController::class, 'completeProfile'])
            ->name('complete_profile');

        // 2. Auth Management
        Route::controller(ThirdPartyAuthController::class)->group(function () {
            Route::post('logout', 'logout')->name('logout');
            Route::post('me', 'me')->name('me');
            Route::post('email/verification-notification', 'resendVerificationEmail')
                ->name('verification.send')
                ->middleware(['throttle:3,1']);
        });

        // 3. Verified-Only Routes
        Route::middleware(['verified'])->group(function () {
            Route::patch('profile', [ThirdPartyProfileController::class, 'update'])
                ->name('profile.update');

            Route::get('validate-token', function (Request $request) {
                $user = $request->user()->load([
                    'thirdParty.types',
                    'thirdParty.status',
                    'thirdParty.businessType',
                    'thirdParty.supplierMaster',
                    'thirdParty.supplierMaster.status'
                ]);

                return response()->json([
                    'valid' => true,
                    'user' => new ThirdPartyUserResource($user),
                ]);
            })->name('validate_token');
        });
    });
});
