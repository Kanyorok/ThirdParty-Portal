<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PendingWorkflowController;
use App\Http\Controllers\Auth\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('home');
    Route::get('/user-dashboard/widgets', [DashboardController::class, 'getWidgets'])->name('user-dashboard.widgets');
    Route::post('/user-dashboard/widgets/save', [DashboardController::class, 'saveLayout'])->name('user-dashboard.widgets.save');
    Route::get('module-search', [DashboardController::class, 'search'])->name('modules.search');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('timeout', [AuthenticatedSessionController::class, 'timeout'])->name('timeout');

    Route::get('user', [ProfileController::class, 'profile'])->name('profile');
    Route::put('user', [ProfileController::class, 'updateUser']);
    Route::post('user', [ProfileController::class, 'updateCred'])->name('profile.cred');
    Route::post('user/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');

    Route::get('pending-workflows', PendingWorkflowController::class)->name('pending-workflows');

    // Route::any('ssrs-report', [\App\Http\Controllers\Auth\ReportController::class, 'show'])->name('ssrs.view_report');
    //Route::any('rpt',[ReportController::class,'view']);//->name('ssrs.view_report');

    //    Route::get('/ssrs-proxy', [SSRSProxyController::class, 'fetchReport']);
    /* Route::get('core/auth/report/{report}/ssrs-report-proxy', SSRSProxyController::class)->name('auth.ssrs.proxy');
     Route::any('report/{any}', [SSRSProxyController::class, 'report'])->where('any', '.*')->name('ssrs.proxy.report')
         ->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
     Route::any('ReportServer/Reserved.ReportViewerWebControl.axd', [SSRSProxyController::class, 'handleAxd'])
         ->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

     Route::any('ReportServer/{any?}', [SSRSProxyController::class, 'preview'])->where('any', '.*')
         ->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
     Route::any('reports/{any}', [SSRSProxyController::class, 'assets'])->where('any', '.*');
     Route::get('core/auth/report/assets/{asset?}', [SSRSProxyController::class, 'assets'])->name('auth.ssrs.proxy.assets');*/
});
