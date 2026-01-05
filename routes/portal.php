<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThirdParty\API\ThirdPartyAuthController;
use App\Http\Controllers\ThirdParty\API\NewThirdPartyController;
use App\Http\Controllers\ThirdParty\API\ProfileController;
use App\Http\Resources\ThirdParty\Api\ThirdPartyUserResource;
use App\Http\Controllers\ThirdParty\API\MetadataController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::prefix('portal/auth')->name('portal.auth.')->group(function () {
    Route::post('register', [NewThirdPartyController::class, 'store'])
        ->name('register')
        ->middleware(['throttle:5,1']);

    Route::post('login', [ThirdPartyAuthController::class, 'login'])
        ->name('login')
        ->middleware(['throttle:10,1']);

    Route::get('metadata/countries', [MetadataController::class, 'getCountries']);
    Route::get('metadata/business-types', [MetadataController::class, 'getBusinessTypes']);
    Route::get('metadata/supplier-categories', [MetadataController::class, 'getSupplierCategories']);
    Route::get('metadata/tenant-types', [MetadataController::class, 'getTenantTypes']);
    Route::get('metadata/localities/{countryId}', [MetadataController::class, 'getLocalities']);
    Route::get('metadata/code-details/{group}', [MetadataController::class, 'getCodeDetails']);

    Route::post('password/forgot', [NewPasswordController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('password/reset', [NewPasswordController::class, 'resetPassword'])->name('password.reset');

    Route::get('email/verify/{id}/{hash}', [ThirdPartyAuthController::class, 'verify'])
        ->name('verification.verify')
        ->middleware(['signed', 'throttle:6,1']);

    Route::controller(ThirdPartyAuthController::class)->middleware(['auth.thirdparty'])->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::get('me', 'me')->name('me');
        Route::post('email/verification-notification', 'resendVerificationEmail')
            ->name('verification.send')
            ->middleware(['throttle:3,1']);
    });

    // Profile management routes
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->middleware(['auth.thirdparty'])->group(function () {
        // Base company profile
        Route::get('/', 'show')->name('show');
        Route::put('/', 'updateProfile')->name('update');

        // Get available profiles for user
        Route::get('/available', 'getAvailableProfiles')->name('available');

        // Supplier profile
        Route::get('/supplier', 'getSupplierProfile')->name('supplier.show');
        Route::put('/supplier', 'updateSupplierProfile')->name('supplier.update');

        // Tenant profile
        Route::get('/tenant', 'getTenantProfile')->name('tenant.show');
        Route::put('/tenant', 'updateTenantProfile')->name('tenant.update');

        // Customer profile
        Route::get('/customer', 'getCustomerProfile')->name('customer.show');
        Route::put('/customer', 'updateCustomerProfile')->name('customer.update');
    });

    Route::middleware(['verified'])->group(function () {
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
