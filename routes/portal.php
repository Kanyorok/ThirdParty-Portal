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

use App\Http\Controllers\Procurement\TenderApiController;
use App\Http\Controllers\Procurement\Prequalification\Api\PrequalificationApplicationController;

use App\Http\Controllers\API\Procurement\SupplierRFQController;
use App\Http\Controllers\API\Procurement\TenderClarificationApiController;
use App\Http\Controllers\API\Procurement\TenderSubmissionApiController;
use App\Http\Controllers\API\Procurement\TenderInvitationResponseApiController;

use App\Http\Resources\ThirdParty\Api\ThirdPartyUserResource;

Route::prefix('portal/auth')->name('portal.auth.')->group(function () {

    Route::post('register', [NewThirdPartyController::class, 'store'])->middleware(['throttle:5,1'])->name('register');
    Route::post('login', [ThirdPartyAuthController::class, 'login'])->middleware(['throttle:10,1'])->name('login');

    Route::get(
        'email/verify/{user}/{hash}',
        [ThirdPartyAuthController::class, 'verifyEmail']
    )->name('portal.auth.email.verify');

    Route::post(
        'email/resend',
        [ThirdPartyAuthController::class, 'resendVerification']
    )->middleware(['throttle:3,10']);

    Route::post('password/forgot', [ThirdPartyPasswordController::class, 'forgotPassword'])->middleware(['throttle:5,1'])->name('password.forgot');
    Route::post('password/reset', [ThirdPartyPasswordController::class, 'resetPassword'])->middleware(['throttle:5,1'])->name('password.reset');

    Route::prefix('lookups')->group(function () {
        Route::get('bulk', [LookupController::class, 'bulk']);
        Route::get('{codeId}', [LookupController::class, '__invoke']);
    });

    Route::prefix('metadata')->group(function () {
        Route::get('countries', [MetadataController::class, 'getCountries']);
        Route::get('business-types', [MetadataController::class, 'getBusinessTypes']);
        Route::get('supplier-categories', [MetadataController::class, 'getSupplierCategories']);
        Route::get('tenant-types', [MetadataController::class, 'getTenantTypes']);
        Route::get('localities/{countryId}', [MetadataController::class, 'getLocalities'])->whereNumber('countryId');
        Route::get('code-details/{group}', [MetadataController::class, 'getCodeDetails']);
    });

    Route::middleware(['auth.thirdparty'])->group(function () {
        Route::post('logout', [ThirdPartyAuthController::class, 'logout']);
        Route::get('me', [ThirdPartyAuthController::class, 'me']);
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
        });
    });

    Route::prefix('profile')->middleware(['auth.thirdparty'])->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'updateProfile']);
        Route::get('available', [ProfileController::class, 'getAvailableProfiles']);
        Route::get('supplier', [ProfileController::class, 'getSupplierProfile']);
        Route::put('supplier', [ProfileController::class, 'updateSupplierProfile']);
        Route::get('tenant', [ProfileController::class, 'getTenantProfile']);
        Route::put('tenant', [ProfileController::class, 'updateTenantProfile']);
        Route::get('customer', [ProfileController::class, 'getCustomerProfile']);
        Route::put('customer', [ProfileController::class, 'updateCustomerProfile']);
    });
});

Route::middleware(['auth.thirdparty'])->prefix('supplier')->group(function () {

    Route::get('rfqs', [SupplierRFQController::class, 'listInvitations']);
    Route::get('rfqs/{rfq}', [SupplierRFQController::class, 'getInvitation'])->whereNumber('rfq');
    Route::get('rfqs/{rfq}/clarifications', [SupplierRFQController::class, 'listClarifications'])->whereNumber('rfq');
    Route::post('rfqs/responses', [SupplierRFQController::class, 'submitResponse']);
    Route::post('rfqs/clarifications', [SupplierRFQController::class, 'postClarification']);

    Route::prefix('prequalification')->group(function () {
        Route::get('rounds', [PrequalificationApplicationController::class, 'apiIndex']);
        Route::get('rounds/{round}', [PrequalificationApplicationController::class, 'apiShow'])->whereNumber('round');
        Route::post('applications', [PrequalificationApplicationController::class, 'store']);
    });

    Route::prefix('tenders')->group(function () {
        Route::get('/', [TenderApiController::class, 'index']);
        Route::get('{tender}', [TenderApiController::class, 'show'])->whereNumber('tender');
        Route::post('respond', [TenderInvitationResponseApiController::class, 'respond']);
    });

    Route::prefix('tender-clarifications')->group(function () {
        Route::get('/', [TenderClarificationApiController::class, 'getClarifications']);
        Route::post('/', [TenderClarificationApiController::class, 'submitClarification']);
    });

    Route::prefix('bid-submissions')->group(function () {
        Route::get('/', [TenderSubmissionApiController::class, 'index']);
        Route::post('/', [TenderSubmissionApiController::class, 'store']);
    });
});
