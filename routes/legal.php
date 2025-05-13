<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legal\CopyrightLicenseAgreementController;
use App\Http\Controllers\Legal\PatentTrackingController;
use App\Http\Controllers\Legal\TradeMarkRegisterController;
use App\Http\Controllers\Legal\ArchiveController;
use App\Http\Controllers\Legal\RegisterController;
use App\Http\Controllers\Legal\ResponseTrackerController;
use App\Http\Controllers\Legal\ExternalDirectoryController;
use App\Http\Controllers\Legal\FeeTrackerController;
use App\Http\Controllers\Legal\PerformanceLogController;
use App\Http\Controllers\Legal\CompliancestatusController;
use App\Http\Controllers\Legal\PendingContractsController;


Route::namespace('Legal')->group(function () {
    Route::resource('copyrightlicenseagreement', CopyrightLicenseAgreementController::class);
    Route::resource('patenttracking', PatentTrackingController::class);
    Route::resource('trademarkregister', TradeMarkRegisterController::class);
    Route::resource('archive', ArchiveController::class);
    Route::resource('register', RegisterController::class);
    Route::resource('responsetracker', ResponseTrackerController::class);
    Route::resource('externaldirectory', ExternalDirectoryController::class);
    Route::resource('feetracker', FeeTrackerController::class);
    Route::resource('performancelog', PerformanceLogController::class);
    Route::resource('compliancestatus', CompliancestatusController::class);
    Route::resource('pendingcontracts', PendingContractsController::class);
});