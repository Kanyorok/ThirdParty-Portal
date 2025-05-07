<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HRMS\EmployeeManagementController;
use App\Http\Controllers\HRMS\LeaveBalanceController;
use App\Http\Controllers\HRMS\LeaveRequestsController;
use App\Http\Controllers\Hrms\AttendanceManagementController;
use App\Http\Controllers\Hrms\PayrollDashboardController;
use App\Http\Controllers\Hrms\GeneratePaySlipController;
use App\Http\Controllers\Hrms\PayRollSettingsController;

Route::namespace('HRMS')->group(function () {
    Route::resource('employeemanagement', EmployeeManagementController::class);
    Route::resource('leavebalance', LeaveBalanceController::class);
    Route::resource('leaverequests', LeaveRequestsController::class);
    Route::resource('attendancemanagement', AttendanceManagementController::class);
    Route::resource('payrolldashboard', PayrollDashboardController::class);
    Route::resource('generatepayslip', GeneratePaySlipController::class);
    Route::resource('payrollsettings', PayRollSettingsController::class);
});