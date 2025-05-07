<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hrms\AttendanceManagementController;
use App\Http\Controllers\Hrms\PayrollDashboardController;
use App\Http\Controllers\Hrms\GeneratePaySlipController;
use App\Http\Controllers\Hrms\PayRollSettingsController;


Route::namespace('Hrms')->group(function () {
    Route::resource('attendancemanagement', AttendanceManagementController::class);
    Route::resource('payrolldashboard', PayrollDashboardController::class);
    Route::resource('generatepayslip', GeneratePaySlipController::class);
    Route::resource('payrollsettings', PayRollSettingsController::class);
});