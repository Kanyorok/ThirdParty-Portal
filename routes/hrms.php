<?php

use App\Http\Controllers\HR\HolidayController;
use App\Http\Controllers\HR\LeaveTypeController;

use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HRM\DepartmentController;
use App\Http\Controllers\HR\BranchConfigController;
use App\Http\Controllers\HR\JobGradeController;
use App\Http\Controllers\HR\JobRoleController;
use App\Http\Controllers\HR\ConfigController;
use App\Http\Controllers\HR\KpiItemController;
use App\Http\Controllers\HR\KpiRatingScaleController;
use App\Http\Controllers\HR\KpiPeriodController;
use App\Http\Controllers\HR\KpiWeightingController;
use App\Http\Controllers\HR\KpiCategoryController;
use App\Http\Controllers\HR\KpiUnitController;
use App\Http\Controllers\HR\KpiFormulaController;
use App\Http\Controllers\HR\PayrollAllowanceController;
use App\Http\Controllers\HR\PayrollAllowanceRuleController;
use App\Http\Controllers\HR\StatutoryNhifController;
use App\Http\Controllers\HR\StatutoryNssfController;
use App\Http\Controllers\HR\StatutoryPayeController;
use App\Http\Controllers\HR\StatutoryReliefController;
use App\Http\Controllers\HR\StatutoryHousingLevyController;
use App\Http\Controllers\HR\StatutoryFringeBenefitController;

Route::middleware(['auth']) // + any HR-specific middleware/permissions
    ->prefix('hr')
    ->name('hr.')
    ->group(function () {

        // Employees
        Route::resource('employees', EmployeeController::class);
        Route::get('employees/{employee}/status', [EmployeeController::class, 'statusForm'])->name('employees.status.edit');
        Route::put('employees/{employee}/status', [EmployeeController::class, 'statusUpdate'])->name('employees.status.update');
        Route::resource('departments', DepartmentController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);

        // Attendance
        Route::get('attendance/daily', [AttendanceController::class, 'daily'])->name('attendance.daily');
        Route::get('attendance/logs', [AttendanceController::class, 'logs'])->name('attendance.logs');
    });

Route::prefix('hr')->name('hr.')->middleware(['auth'])->group(function () {

    // ... existing employees & attendance routes

    // HR Config – Holidays
    Route::get('config/holidays', [HolidayController::class, 'index'])->name('config.holidays.index');
    Route::get('config/holidays/create', [HolidayController::class, 'create'])->name('config.holidays.create');
    Route::post('config/holidays', [HolidayController::class, 'store'])->name('config.holidays.store');
    Route::get('config/holidays/{id}/edit', [HolidayController::class, 'edit'])->name('config.holidays.edit');
    Route::put('config/holidays/{id}', [HolidayController::class, 'update'])->name('config.holidays.update');
    Route::delete('config/holidays/{id}', [HolidayController::class, 'destroy'])->name('config.holidays.destroy');
    Route::post('config/holidays/{id}/approve', [HolidayController::class, 'approve'])->name('config.holidays.approve');

    // HR Config – Leave Types
    Route::get('config/leave-types', [LeaveTypeController::class, 'index'])->name('config.leavetypes.index');
    Route::get('config/leave-types/create', [LeaveTypeController::class, 'create'])->name('config.leavetypes.create');
    Route::post('config/leave-types', [LeaveTypeController::class, 'store'])->name('config.leavetypes.store');
    Route::get('config/leave-types/{id}/edit', [LeaveTypeController::class, 'edit'])->name('config.leavetypes.edit');
    Route::put('config/leave-types/{id}', [LeaveTypeController::class, 'update'])->name('config.leavetypes.update');
    Route::delete('config/leave-types/{id}', [LeaveTypeController::class, 'destroy'])->name('config.leavetypes.destroy');
    Route::post('config/leave-types/{id}/approve', [LeaveTypeController::class, 'approve'])->name('config.leavetypes.approve');

    // HR Config — Org profile & working days
    Route::get('config/org-profile', [ConfigController::class, 'orgProfile'])->name('config.org.index');
    Route::post('config/org-profile', [ConfigController::class, 'orgProfileUpdate'])->name('config.org.update');
    Route::get('config/working-days', [ConfigController::class, 'workingDays'])->name('config.workingdays.index');
    Route::get('config/working-days/view', [ConfigController::class, 'workingDaysView'])->name('config.workingdays.view');
    Route::post('config/working-days', [ConfigController::class, 'workingDaysUpdate'])->name('config.workingdays.update');

    // HR Config — Branches
    Route::resource('config/branches', BranchConfigController::class)->names('config.branches')->except(['show']);

    // HR Config — Job Grades & Roles
    Route::resource('config/job-grades', JobGradeController::class)->names('config.jobgrades')->except(['show']);
    Route::resource('config/job-roles', JobRoleController::class)->names('config.jobroles')->except(['show']);

    // HR Config — KPI setup (stubs)
    Route::resource('config/kpi/library', KpiItemController::class)->names('config.kpi.library')->except(['show']);
    Route::resource('config/kpi/categories', KpiCategoryController::class)->names('config.kpi.categories')->except(['show']);
    Route::resource('config/kpi/units', KpiUnitController::class)->names('config.kpi.units')->except(['show']);
    Route::resource('config/kpi/rating-scales', KpiRatingScaleController::class)->names('config.kpi.ratingscales')->except(['show']);
    Route::resource('config/kpi/periods', KpiPeriodController::class)->names('config.kpi.periods')->except(['show']);
    Route::resource('config/kpi/weighting', KpiWeightingController::class)->names('config.kpi.weighting')->except(['show']);
    Route::resource('config/kpi/formulas', KpiFormulaController::class)->names('config.kpi.formulas')->except(['show']);

    // Statutory & Payroll Rules
    Route::resource('statutory/deductions', \App\Http\Controllers\HR\PayrollDeductionController::class)->names('statutory.deductions')->except(['show']);
    Route::resource('statutory/deductions/{deduction}/rules', \App\Http\Controllers\HR\PayrollDeductionRuleController::class)->names('statutory.deductions.rules')->except(['show']);
    Route::resource('statutory/allowances', PayrollAllowanceController::class)->names('statutory.allowances')->except(['show']);
    Route::resource('statutory/allowances/{allowance}/rules', PayrollAllowanceRuleController::class)->names('statutory.allowances.rules')->except(['show']);

    // Employee Movements
    Route::resource('movements/promotions', \App\Http\Controllers\HR\EmployeePromotionController::class)->names('movements.promotions');
    Route::post('movements/promotions/{promotion}/approve', [\App\Http\Controllers\HR\EmployeePromotionController::class, 'approve'])->name('movements.promotions.approve');
    Route::post('movements/promotions/{promotion}/reject', [\App\Http\Controllers\HR\EmployeePromotionController::class, 'reject'])->name('movements.promotions.reject');

    Route::resource('movements/demotions', \App\Http\Controllers\HR\EmployeeDemotionController::class)->names('movements.demotions');
    Route::post('movements/demotions/{demotion}/approve', [\App\Http\Controllers\HR\EmployeeDemotionController::class, 'approve'])->name('movements.demotions.approve');
    Route::post('movements/demotions/{demotion}/reject', [\App\Http\Controllers\HR\EmployeeDemotionController::class, 'reject'])->name('movements.demotions.reject');

    Route::resource('movements/transfers', \App\Http\Controllers\HR\EmployeeTransferController::class)->names('movements.transfers');
    Route::post('movements/transfers/{transfer}/approve', [\App\Http\Controllers\HR\EmployeeTransferController::class, 'approve'])->name('movements.transfers.approve');
    Route::post('movements/transfers/{transfer}/reject', [\App\Http\Controllers\HR\EmployeeTransferController::class, 'reject'])->name('movements.transfers.reject');

    Route::resource('movements/acting', \App\Http\Controllers\HR\EmployeeActingAssignmentController::class)->names('movements.acting');
    Route::post('movements/acting/{acting}/approve', [\App\Http\Controllers\HR\EmployeeActingAssignmentController::class, 'approve'])->name('movements.acting.approve');
    Route::post('movements/acting/{acting}/reject', [\App\Http\Controllers\HR\EmployeeActingAssignmentController::class, 'reject'])->name('movements.acting.reject');

    // Time & Attendance
    Route::resource('attendance/devices', \App\Http\Controllers\HR\AttendanceDeviceController::class)->names('attendance.devices')->except(['show']);
    Route::get('attendance/logs', [\App\Http\Controllers\HR\AttendanceRawLogController::class, 'index'])->name('attendance.logs.index');
    Route::get('attendance/daily', [\App\Http\Controllers\HR\AttendanceDailySummaryController::class, 'index'])->name('attendance.daily.index');
    Route::get('attendance/overtime', [\App\Http\Controllers\HR\OvertimeRequestController::class, 'index'])->name('attendance.overtime.index');
    Route::get('attendance/overtime/create', [\App\Http\Controllers\HR\OvertimeRequestController::class, 'create'])->name('attendance.overtime.create');
    Route::post('attendance/overtime', [\App\Http\Controllers\HR\OvertimeRequestController::class, 'store'])->name('attendance.overtime.store');
    Route::post('attendance/overtime/{id}/approve', [\App\Http\Controllers\HR\OvertimeRequestController::class, 'approve'])->name('attendance.overtime.approve');
    Route::post('attendance/overtime/{id}/reject', [\App\Http\Controllers\HR\OvertimeRequestController::class, 'reject'])->name('attendance.overtime.reject');
    Route::get('attendance/exceptions', [\App\Http\Controllers\HR\AttendanceExceptionController::class, 'index'])->name('attendance.exceptions.index');
    Route::post('attendance/exceptions/{id}/resolve', [\App\Http\Controllers\HR\AttendanceExceptionController::class, 'resolve'])->name('attendance.exceptions.resolve');

    // Leave Management
    Route::resource('leave/requests', \App\Http\Controllers\HR\LeaveRequestController::class)->names('leave.requests')->only(['index','create','store']);
    Route::post('leave/requests/{id}/approve', [\App\Http\Controllers\HR\LeaveRequestController::class, 'approve'])->name('leave.requests.approve');
    Route::post('leave/requests/{id}/reject', [\App\Http\Controllers\HR\LeaveRequestController::class, 'reject'])->name('leave.requests.reject');
    Route::post('leave/requests/{id}/cancel', [\App\Http\Controllers\HR\LeaveRequestController::class, 'cancel'])->name('leave.requests.cancel');
    Route::get('leave/balances', [\App\Http\Controllers\HR\LeaveBalanceController::class, 'index'])->name('leave.balances.index');
    Route::post('leave/balances/accrue', [\App\Http\Controllers\HR\LeaveBalanceController::class, 'accrueMonthly'])->name('leave.balances.accrue');
});
