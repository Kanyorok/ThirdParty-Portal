<?php

use Illuminate\Support\Facades\Route;

Route::namespace('HRM')->prefix('hrm')->group(function () {

    Route::resource('departments', \App\Http\Controllers\HRM\DepartmentController::class)->except(['edit']);

    Route::resource('employees', \App\Http\Controllers\HRM\EmployeeController::class)->except(['edit']);

    /*  Route::resource('employeemanagement', EmployeeManagementController::class);
    Route::resource('leavebalance', LeaveBalanceController::class);
    Route::resource('leaverequests', LeaveRequestsController::class);
    Route::resource('attendancemanagement', AttendanceManagementController::class);
    Route::resource('payrolldashboard', PayrollDashboardController::class);
    Route::resource('generatepayslip', GeneratePaySlipController::class);
    Route::resource('payrollsettings', PayRollSettingsController::class);*/

});

    // Committees with both 'hrm' URL prefix and 'hrms.' name prefix
    Route::namespace('HRM')->prefix('hrm')->name('hrms.')->group(function () {

        Route::resource('committees', \App\Http\Controllers\HRM\CommitteeController::class);

});
