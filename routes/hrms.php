<?php


use App\Http\Controllers\HRM\DepartmentController;
use Illuminate\Support\Facades\Route;

Route::namespace('HRM')->prefix('hrm')->group(function () {

    Route::resource('departments', DepartmentController::class)->except(['edit']);

    /*  Route::resource('employeemanagement', EmployeeManagementController::class);
    Route::resource('leavebalance', LeaveBalanceController::class);
    Route::resource('leaverequests', LeaveRequestsController::class);
    Route::resource('attendancemanagement', AttendanceManagementController::class);
    Route::resource('payrolldashboard', PayrollDashboardController::class);
    Route::resource('generatepayslip', GeneratePaySlipController::class);
    Route::resource('payrollsettings', PayRollSettingsController::class);*/
});
