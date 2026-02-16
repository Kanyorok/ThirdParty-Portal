<?php

use App\Http\Controllers\HRM\EmployeeInternalCommitteeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['module:1000000'])->namespace('HRM')->prefix('hrm')->group(function () {

    Route::resource('departments', \App\Http\Controllers\HRM\DepartmentController::class)->except(['edit']);

    Route::resource('employees', \App\Http\Controllers\HRM\EmployeeController::class)->except(['edit']);
    Route::resource('employeescommittee', EmployeeInternalCommitteeController::class);
    Route::delete('/employeescommittee/remove', [EmployeeInternalCommitteeController::class, 'remove'])->name('employeescommittee.remove');
    /*  Route::resource('employeemanagement', EmployeeManagementController::class);
    Route::resource('leavebalance', LeaveBalanceController::class);
    Route::resource('leaverequests', LeaveRequestsController::class);
    Route::resource('attendancemanagement', AttendanceManagementController::class);
    Route::resource('payrolldashboard', PayrollDashboardController::class);
    Route::resource('generatepayslip', GeneratePaySlipController::class);
    Route::resource('payrollsettings', PayRollSettingsController::class);*/

});
