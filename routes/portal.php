<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\ThirdParty\API\NewThirdPartyController;
use App\Http\Controllers\ThirdParty\API\ProfileController;
use App\Http\Controllers\ThirdParty\API\MetadataController;
use App\Http\Controllers\ThirdParty\API\ThirdPartyAuthController;
use App\Http\Controllers\ThirdParty\API\LookupController;
use App\Http\Controllers\ThirdParty\API\ThirdPartyPasswordController;

use App\Http\Controllers\Procurement\Prequalification\Api\PrequalificationRoundController;
use App\Http\Controllers\Procurement\Prequalification\Api\PreqApplicationController;

use App\Http\Resources\ThirdParty\Api\ThirdPartyUserResource;

Route::prefix('portal/auth')->name('portal.auth.')->group(function () {

    Route::post('register', [NewThirdPartyController::class, 'store'])
        ->middleware(['throttle:5,1'])
        ->name('register');

    Route::post('login', [ThirdPartyAuthController::class, 'login'])
        ->middleware(['throttle:10,1'])
        ->name('login');

    Route::get('verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return response()->json(['message' => 'Email verified successfully.']);
    })->middleware(['signed'])->name('verification.verify');

    Route::post('verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent!']);
    })->middleware(['auth.thirdparty', 'throttle:6,1'])->name('verification.send');

    Route::post('password/forgot', [ThirdPartyPasswordController::class, 'forgotPassword'])
        ->name('password.forgot');

    Route::post('password/reset', [ThirdPartyPasswordController::class, 'resetPassword'])
        ->name('password.reset');

    Route::prefix('lookups')->name('lookups.')->group(function () {
        Route::get('bulk', [LookupController::class, 'bulk'])->name('bulk');
        Route::get('{codeId}', [LookupController::class, '__invoke'])->name('show');
    });

    Route::get('metadata/countries', [MetadataController::class, 'getCountries']);
    Route::get('metadata/business-types', [MetadataController::class, 'getBusinessTypes']);
    Route::get('metadata/supplier-categories', [MetadataController::class, 'getSupplierCategories']);
    Route::get('metadata/tenant-types', [MetadataController::class, 'getTenantTypes']);
    Route::get('metadata/localities/{countryId}', [MetadataController::class, 'getLocalities']);
    Route::get('metadata/code-details/{group}', [MetadataController::class, 'getCodeDetails']);

    Route::controller(ThirdPartyAuthController::class)
        ->middleware(['auth.thirdparty'])
        ->group(function () {
            Route::post('logout', 'logout')->name('logout');
            Route::get('me', 'me')->name('me');
        });

    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->name('profile.')
        ->middleware(['auth.thirdparty'])
        ->group(function () {
            Route::get('/', 'show')->name('show');
            Route::put('/', 'updateProfile')->name('update');
            Route::get('/available', 'getAvailableProfiles')->name('available');
            Route::get('/supplier', 'getSupplierProfile')->name('supplier.show');
            Route::put('/supplier', 'updateSupplierProfile')->name('supplier.update');
            Route::get('/tenant', 'getTenantProfile')->name('tenant.show');
            Route::put('/tenant', 'updateTenantProfile')->name('tenant.update');
            Route::get('/customer', 'getCustomerProfile')->name('customer.show');
            Route::put('/customer', 'updateCustomerProfile')->name('customer.update');
        });

    Route::middleware(['auth.thirdparty'])->group(function () {
        Route::get('validate-token', function (Request $request) {
            $user = $request->user()->load([
                'thirdParty.types',
                'thirdParty.status',
                'thirdParty.businessType',
                'thirdParty.supplierMaster',
                'thirdParty.supplierMaster.status',
            ]);

            return response()->json([
                'valid' => true,
                'user' => new ThirdPartyUserResource($user),
            ]);
        })->name('validate_token');
    });
});

Route::middleware(['auth.thirdparty'])->group(function () {
    Route::prefix('prequalification')->name('prequalification.')->group(function () {
        Route::get('applications', [PreqApplicationController::class, 'apiIndex'])->name('applications.index');
        Route::post('applications', [PreqApplicationController::class, 'store'])->name('applications.store');
    });
});