<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HRMS\EmployeeManagementController;
use App\Http\Controllers\HRMS\LeaveBalanceController;
use App\Http\Controllers\HRMS\LeaveRequestsController;

Route::namespace('HRMS')->group(function () {
    Route::resource('employeemanagement', EmployeeManagementController::class);
    Route::resource('leavebalance', LeaveBalanceController::class);
    Route::resource('leaverequests', LeaveRequestsController::class);
});