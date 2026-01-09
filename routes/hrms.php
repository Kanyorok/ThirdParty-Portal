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
use App\Http\Controllers\HR\KpiScoreChartController;
use App\Http\Controllers\HR\KpiPeriodController;
use App\Http\Controllers\HR\KpiWeightingController;
use App\Http\Controllers\HR\KpiCategoryController;
use App\Http\Controllers\HR\KpiUnitController;
use App\Http\Controllers\HR\KpiFormulaController;
use App\Http\Controllers\HR\KpiPerspectiveController;
use App\Http\Controllers\HR\KpiPerspectiveWeightController;
use App\Http\Controllers\HR\KpiGoalController;
use App\Http\Controllers\HR\KpiAppraisalController;
use App\Http\Controllers\HR\KpiReportController;
use App\Http\Controllers\HR\ApplicantController;
use App\Http\Controllers\HR\JobApplicationController;
use App\Http\Controllers\HR\JobInterviewController;
use App\Http\Controllers\HR\JobInterviewQuestionController;
use App\Http\Controllers\HR\JobInterviewQuestionGroupController;
use App\Http\Controllers\HR\JobOfferController;
use App\Http\Controllers\HR\JobOpeningController;
use App\Http\Controllers\HR\JobRequisitionController;
use App\Http\Controllers\HR\OnboardingController;
use App\Http\Controllers\HR\DisciplinaryLegalRefController;
use App\Http\Controllers\HR\DisciplinaryPolicyController;
use App\Http\Controllers\HR\DisciplinaryOffenceCategoryController;
use App\Http\Controllers\HR\DisciplinaryOffenceController;
use App\Http\Controllers\HR\DisciplinarySanctionController;
use App\Http\Controllers\HR\DisciplinaryLetterTemplateController;
use App\Http\Controllers\HR\DisciplinaryCaseController;
use App\Http\Controllers\HR\DisciplinaryNoticeController;
use App\Http\Controllers\HR\DisciplinaryResponseController;
use App\Http\Controllers\HR\DisciplinaryInvestigationController;
use App\Http\Controllers\HR\DisciplinaryHearingController;
use App\Http\Controllers\HR\DisciplinaryDecisionController;
use App\Http\Controllers\HR\DisciplinaryAppealController;
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
    Route::resource('config/kpi/perspectives', KpiPerspectiveController::class)->names('config.kpi.perspectives')->except(['show']);
    Route::resource('config/kpi/perspective-weights', KpiPerspectiveWeightController::class)->names('config.kpi.perspective-weights')->except(['show']);
    Route::resource('config/kpi/rating-scales', KpiRatingScaleController::class)->names('config.kpi.ratingscales')->except(['show']);
    Route::resource('config/kpi/score-charts', KpiScoreChartController::class)->names('config.kpi.scorecharts')->except(['show']);
    Route::resource('config/kpi/periods', KpiPeriodController::class)->names('config.kpi.periods')->except(['show']);
    Route::resource('config/kpi/weighting', KpiWeightingController::class)->names('config.kpi.weighting')->except(['show']);
    Route::resource('config/kpi/formulas', KpiFormulaController::class)->names('config.kpi.formulas')->except(['show']);

    // KPI Performance
    Route::get('kpi/goals', [KpiGoalController::class, 'index'])->name('kpi.goals.index');
    Route::get('kpi/goals/create', [KpiGoalController::class, 'create'])->name('kpi.goals.create');
    Route::post('kpi/goals', [KpiGoalController::class, 'store'])->name('kpi.goals.store');
    Route::get('kpi/goals/{id}', [KpiGoalController::class, 'show'])->name('kpi.goals.show');
    Route::get('kpi/goals/{id}/edit', [KpiGoalController::class, 'edit'])->name('kpi.goals.edit');
    Route::put('kpi/goals/{id}', [KpiGoalController::class, 'update'])->name('kpi.goals.update');
    Route::post('kpi/goals/{id}/submit', [KpiGoalController::class, 'submit'])->name('kpi.goals.submit');
    Route::post('kpi/goals/{id}/approve', [KpiGoalController::class, 'approve'])->name('kpi.goals.approve');
    Route::post('kpi/goals/{id}/reject', [KpiGoalController::class, 'reject'])->name('kpi.goals.reject');
    Route::post('kpi/goals/{id}/return', [KpiGoalController::class, 'return'])->name('kpi.goals.return');

    Route::get('kpi/appraisals', [KpiAppraisalController::class, 'index'])->name('kpi.appraisals.index');
    Route::get('kpi/appraisals/create', [KpiAppraisalController::class, 'create'])->name('kpi.appraisals.create');
    Route::post('kpi/appraisals', [KpiAppraisalController::class, 'store'])->name('kpi.appraisals.store');
    Route::get('kpi/appraisals/{id}', [KpiAppraisalController::class, 'show'])->name('kpi.appraisals.show');
    Route::get('kpi/appraisals/{id}/edit', [KpiAppraisalController::class, 'edit'])->name('kpi.appraisals.edit');
    Route::put('kpi/appraisals/{id}', [KpiAppraisalController::class, 'update'])->name('kpi.appraisals.update');
    Route::post('kpi/appraisals/{id}/submit', [KpiAppraisalController::class, 'submit'])->name('kpi.appraisals.submit');
    Route::post('kpi/appraisals/{id}/approve', [KpiAppraisalController::class, 'approve'])->name('kpi.appraisals.approve');
    Route::post('kpi/appraisals/{id}/reject', [KpiAppraisalController::class, 'reject'])->name('kpi.appraisals.reject');

    Route::get('kpi/reports', [KpiReportController::class, 'index'])->name('kpi.reports.index');

    // Statutory & Payroll Rules
    Route::resource('statutory/deductions', \App\Http\Controllers\HR\PayrollDeductionController::class)->names('statutory.deductions')->except(['show']);
    Route::resource('statutory/deductions/{deduction}/rules', \App\Http\Controllers\HR\PayrollDeductionRuleController::class)->names('statutory.deductions.rules')->except(['show']);
    Route::resource('statutory/allowances', PayrollAllowanceController::class)->names('statutory.allowances')->except(['show']);
    Route::resource('statutory/allowances/{allowance}/rules', PayrollAllowanceRuleController::class)->names('statutory.allowances.rules')->except(['show']);
    Route::resource('statutory/reliefs', \App\Http\Controllers\HR\StatutoryReliefController::class)->names('statutory.reliefs')->except(['show']);

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

    // Recruitment & Onboarding
    Route::resource('recruitment/requisitions', JobRequisitionController::class)->names('recruitment.requisitions')->except(['show', 'destroy']);
    Route::post('recruitment/requisitions/{id}/approve', [JobRequisitionController::class, 'approve'])->name('recruitment.requisitions.approve');
    Route::post('recruitment/requisitions/{id}/reject', [JobRequisitionController::class, 'reject'])->name('recruitment.requisitions.reject');
    Route::post('recruitment/requisitions/{id}/close', [JobRequisitionController::class, 'close'])->name('recruitment.requisitions.close');

    Route::resource('recruitment/openings', JobOpeningController::class)->names('recruitment.openings')->except(['destroy']);
    Route::post('recruitment/openings/{id}/close', [JobOpeningController::class, 'close'])->name('recruitment.openings.close');

    Route::get('recruitment/applicants', [ApplicantController::class, 'index'])->name('recruitment.applicants.index');
    Route::get('recruitment/applicants/{id}', [ApplicantController::class, 'show'])->name('recruitment.applicants.show');

    Route::resource('recruitment/applications', JobApplicationController::class)->names('recruitment.applications')->only(['index', 'create', 'store', 'show']);
    Route::post('recruitment/applications/{id}/screen', [JobApplicationController::class, 'screen'])->name('recruitment.applications.screen');
    Route::post('recruitment/applications/{id}/shortlist', [JobApplicationController::class, 'shortlist'])->name('recruitment.applications.shortlist');
    Route::post('recruitment/applications/{id}/approve-shortlist', [JobApplicationController::class, 'approveShortlist'])->name('recruitment.applications.approveShortlist');
    Route::post('recruitment/applications/{id}/reject', [JobApplicationController::class, 'reject'])->name('recruitment.applications.reject');

    Route::resource('recruitment/interviews', JobInterviewController::class)->names('recruitment.interviews')->except(['destroy']);
    Route::post('recruitment/interviews/{id}/panel', [JobInterviewController::class, 'addPanel'])->name('recruitment.interviews.panel.add');
    Route::post('recruitment/interviews/{id}/panel/{panelId}/remove', [JobInterviewController::class, 'removePanel'])->name('recruitment.interviews.panel.remove');
    Route::post('recruitment/interviews/{id}/questions', [JobInterviewController::class, 'updateQuestions'])->name('recruitment.interviews.questions.update');
    Route::post('recruitment/interviews/{id}/assign-questions', [JobInterviewController::class, 'assignQuestions'])->name('recruitment.interviews.questions.assign');
    Route::get('recruitment/interviews/{id}/candidates/{candidateId}', [JobInterviewController::class, 'evaluate'])->name('recruitment.interviews.candidates.evaluate');
    Route::post('recruitment/interviews/{id}/candidates/{candidateId}/scores', [JobInterviewController::class, 'submitCandidateScores'])->name('recruitment.interviews.candidates.scores');
    Route::post('recruitment/interviews/{id}/candidates/bulk', [JobInterviewController::class, 'bulkCandidateAction'])->name('recruitment.interviews.candidates.bulk');

    Route::prefix('discipline')->name('discipline.')->group(function () {
        Route::resource('legal-refs', DisciplinaryLegalRefController::class)->except(['show', 'destroy']);
        Route::resource('policies', DisciplinaryPolicyController::class)->except(['show', 'destroy']);
        Route::resource('offence-categories', DisciplinaryOffenceCategoryController::class)->except(['show', 'destroy']);
        Route::resource('offences', DisciplinaryOffenceController::class)->except(['show', 'destroy']);
        Route::resource('sanctions', DisciplinarySanctionController::class)->except(['show', 'destroy']);
        Route::resource('letter-templates', DisciplinaryLetterTemplateController::class)->except(['show', 'destroy']);

        Route::resource('cases', DisciplinaryCaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('cases/{case}/documents', [DisciplinaryCaseController::class, 'storeDocument'])->name('cases.documents.store');
        Route::post('cases/{case}/close', [DisciplinaryCaseController::class, 'close'])->name('cases.close');

        Route::get('cases/{case}/notice', [DisciplinaryNoticeController::class, 'create'])->name('cases.notice.create');
        Route::post('cases/{case}/notice', [DisciplinaryNoticeController::class, 'store'])->name('cases.notice.store');
        Route::get('cases/{case}/response', [DisciplinaryResponseController::class, 'create'])->name('cases.response.create');
        Route::post('cases/{case}/response', [DisciplinaryResponseController::class, 'store'])->name('cases.response.store');

        Route::get('cases/{case}/investigation', [DisciplinaryInvestigationController::class, 'edit'])->name('cases.investigation.edit');
        Route::post('cases/{case}/investigation', [DisciplinaryInvestigationController::class, 'store'])->name('cases.investigation.store');
        Route::post('cases/{case}/investigation/approve', [DisciplinaryInvestigationController::class, 'approve'])->name('cases.investigation.approve');
        Route::post('cases/{case}/investigation/documents', [DisciplinaryInvestigationController::class, 'storeDocument'])->name('cases.investigation.documents.store');

        Route::get('cases/{case}/hearing', [DisciplinaryHearingController::class, 'edit'])->name('cases.hearing.edit');
        Route::post('cases/{case}/hearing', [DisciplinaryHearingController::class, 'store'])->name('cases.hearing.store');
        Route::post('cases/{case}/hearing/panel', [DisciplinaryHearingController::class, 'addPanel'])->name('cases.hearing.panel');
        Route::post('cases/{case}/hearing/minutes', [DisciplinaryHearingController::class, 'storeMinutes'])->name('cases.hearing.minutes');

        Route::get('cases/{case}/decision', [DisciplinaryDecisionController::class, 'edit'])->name('cases.decision.edit');
        Route::post('cases/{case}/decision', [DisciplinaryDecisionController::class, 'store'])->name('cases.decision.store');

        Route::get('cases/{case}/appeal', [DisciplinaryAppealController::class, 'edit'])->name('cases.appeal.edit');
        Route::post('cases/{case}/appeal', [DisciplinaryAppealController::class, 'store'])->name('cases.appeal.store');
        Route::post('cases/{case}/appeal/decision', [DisciplinaryAppealController::class, 'decide'])->name('cases.appeal.decide');
    });

    Route::resource('recruitment/interview-question-groups', JobInterviewQuestionGroupController::class)
        ->names('recruitment.interview-question-groups')
        ->except(['show', 'destroy']);
    Route::resource('recruitment/interview-questions', JobInterviewQuestionController::class)
        ->names('recruitment.interview-questions')
        ->except(['show', 'destroy']);

    Route::resource('recruitment/offers', JobOfferController::class)->names('recruitment.offers')->except(['destroy']);
    Route::post('recruitment/offers/{id}/approve', [JobOfferController::class, 'approve'])->name('recruitment.offers.approve');
    Route::post('recruitment/offers/{id}/send', [JobOfferController::class, 'send'])->name('recruitment.offers.send');
    Route::post('recruitment/offers/{id}/accept', [JobOfferController::class, 'accept'])->name('recruitment.offers.accept');
    Route::post('recruitment/offers/{id}/reject', [JobOfferController::class, 'reject'])->name('recruitment.offers.reject');

    Route::get('recruitment/onboarding', [OnboardingController::class, 'index'])->name('recruitment.onboarding.index');
    Route::get('recruitment/onboarding/{id}', [OnboardingController::class, 'show'])->name('recruitment.onboarding.show');
    Route::post('recruitment/onboarding/{id}/tasks', [OnboardingController::class, 'addTask'])->name('recruitment.onboarding.tasks.add');
    Route::post('recruitment/onboarding/{id}/tasks/{taskId}/complete', [OnboardingController::class, 'completeTask'])->name('recruitment.onboarding.tasks.complete');
    Route::post('recruitment/onboarding/{id}/convert', [OnboardingController::class, 'convert'])->name('recruitment.onboarding.convert');

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
    Route::post('leave/requests/calc-days', [\App\Http\Controllers\HR\LeaveRequestController::class, 'previewDays'])->name('leave.requests.calc');
    Route::resource('leave/requests', \App\Http\Controllers\HR\LeaveRequestController::class)->names('leave.requests')->only(['index','create','store']);
    Route::post('leave/requests/{id}/approve', [\App\Http\Controllers\HR\LeaveRequestController::class, 'approve'])->name('leave.requests.approve');
    Route::post('leave/requests/{id}/reject', [\App\Http\Controllers\HR\LeaveRequestController::class, 'reject'])->name('leave.requests.reject');
    Route::post('leave/requests/{id}/cancel', [\App\Http\Controllers\HR\LeaveRequestController::class, 'cancel'])->name('leave.requests.cancel');
    Route::get('leave/balances', [\App\Http\Controllers\HR\LeaveBalanceController::class, 'index'])->name('leave.balances.index');
    Route::post('leave/balances/accrue', [\App\Http\Controllers\HR\LeaveBalanceController::class, 'accrueMonthly'])->name('leave.balances.accrue');
    Route::get('leave/calendar', [\App\Http\Controllers\HR\LeaveRequestController::class, 'calendar'])->name('leave.calendar.index');

    // Payroll Management
    Route::get('payroll', [\App\Http\Controllers\HR\PayrollDashboardController::class, 'index'])->name('payroll.dashboard');
    Route::post('payroll/sync-mandatory', [\App\Http\Controllers\HR\PayrollDashboardController::class, 'syncMandatory'])->name('payroll.syncMandatory');
    Route::get('payroll/gl-setup', [\App\Http\Controllers\HR\PayrollGLSetupController::class, 'edit'])->name('payroll.glsetup.edit');
    Route::post('payroll/gl-setup', [\App\Http\Controllers\HR\PayrollGLSetupController::class, 'update'])->name('payroll.glsetup.update');
    Route::resource('payroll/cycles', \App\Http\Controllers\HR\PayrollCycleController::class)->names('payroll.cycles')->only(['index','create','store','show']);
    Route::post('payroll/cycles/{id}/close', [\App\Http\Controllers\HR\PayrollCycleController::class, 'close'])->name('payroll.cycles.close');
    Route::post('payroll/cycles/{id}/reopen', [\App\Http\Controllers\HR\PayrollCycleController::class, 'reopen'])->name('payroll.cycles.reopen');

    Route::resource('payroll/runs', \App\Http\Controllers\HR\PayrollRunController::class)->names('payroll.runs')->only(['index','create','store','show']);
    Route::post('payroll/runs/{id}/post-finance', [\App\Http\Controllers\HR\PayrollRunController::class, 'postToFinance'])->name('payroll.runs.postFinance');
    Route::post('payroll/runs/{id}/approve', [\App\Http\Controllers\HR\PayrollRunController::class, 'approve'])->name('payroll.runs.approve');
    Route::post('payroll/runs/{id}/reject', [\App\Http\Controllers\HR\PayrollRunController::class, 'reject'])->name('payroll.runs.reject');
    Route::post('payroll/runs/{id}/employees/{employeeId}/recalc', [\App\Http\Controllers\HR\PayrollRunController::class, 'recalcLine'])->name('payroll.runs.recalc');
    Route::get('payroll/runs/{id}/bank-file', [\App\Http\Controllers\HR\PayrollRunController::class, 'bankFile'])->name('payroll.runs.bankfile');
    Route::get('payroll/runs/{id}/eft', [\App\Http\Controllers\HR\PayrollRunController::class, 'eftXml'])->name('payroll.runs.eft');
    Route::get('payroll/runs/{id}/employees/{employeeId}/payslip', [\App\Http\Controllers\HR\PayrollRunController::class, 'payslip'])->name('payroll.runs.payslip');
    Route::get('payroll/runs/{id}/employees/{employeeId}/p9', [\App\Http\Controllers\HR\PayrollRunController::class, 'p9'])->name('payroll.runs.p9');
    Route::get('payroll/runs/{id}/reports/summary', [\App\Http\Controllers\HR\PayrollRunController::class, 'companySummary'])->name('payroll.reports.summary');
    Route::get('payroll/runs/{id}/reports/master', [\App\Http\Controllers\HR\PayrollRunController::class, 'masterRegister'])->name('payroll.reports.master');
    Route::get('payroll/runs/{id}/reports/branches', [\App\Http\Controllers\HR\PayrollRunController::class, 'branchSummary'])->name('payroll.reports.branches');
    Route::get('payroll/runs/{id}/reports/departments', [\App\Http\Controllers\HR\PayrollRunController::class, 'departmentSummary'])->name('payroll.reports.departments');
    Route::get('payroll/runs/{id}/returns', [\App\Http\Controllers\HR\PayrollRunController::class, 'statutoryReturns'])->name('payroll.returns.index');
    Route::get('payroll/runs/{id}/returns/{code}', [\App\Http\Controllers\HR\PayrollRunController::class, 'statutoryReturn'])->name('payroll.returns.show');

    Route::post('payroll/adjustments/{id}/approve', [\App\Http\Controllers\HR\SalaryAdjustmentController::class, 'approve'])->name('payroll.adjustments.approve');
    Route::post('payroll/adjustments/{id}/reject', [\App\Http\Controllers\HR\SalaryAdjustmentController::class, 'reject'])->name('payroll.adjustments.reject');
    Route::resource('payroll/adjustments', \App\Http\Controllers\HR\SalaryAdjustmentController::class)->names('payroll.adjustments')->only(['index','create','store']);
    Route::post('payroll/allowances/{id}/approve', [\App\Http\Controllers\HR\MonthlyAllowanceController::class, 'approve'])->name('payroll.allowances.approve');
    Route::post('payroll/allowances/{id}/reject', [\App\Http\Controllers\HR\MonthlyAllowanceController::class, 'reject'])->name('payroll.allowances.reject');
    Route::resource('payroll/allowances', \App\Http\Controllers\HR\MonthlyAllowanceController::class)->names('payroll.allowances')->only(['index','create','store','destroy']);
    Route::post('payroll/deductions/{id}/approve', [\App\Http\Controllers\HR\MonthlyDeductionController::class, 'approve'])->name('payroll.deductions.approve');
    Route::post('payroll/deductions/{id}/reject', [\App\Http\Controllers\HR\MonthlyDeductionController::class, 'reject'])->name('payroll.deductions.reject');
    Route::resource('payroll/deductions', \App\Http\Controllers\HR\MonthlyDeductionController::class)->names('payroll.deductions')->only(['index','create','store']);
    Route::post('payroll/loans/{id}/approve', [\App\Http\Controllers\HR\StaffLoanController::class, 'approve'])->name('payroll.loans.approve');
    Route::post('payroll/loans/{id}/reject', [\App\Http\Controllers\HR\StaffLoanController::class, 'reject'])->name('payroll.loans.reject');
    Route::resource('payroll/loans', \App\Http\Controllers\HR\StaffLoanController::class)->names('payroll.loans')->only(['index','create','store','show']);
    Route::get('payroll/gratuity', [\App\Http\Controllers\HR\GratuityController::class, 'index'])->name('payroll.gratuity.index');
    Route::get('payroll/gratuity/create', [\App\Http\Controllers\HR\GratuityController::class, 'create'])->name('payroll.gratuity.create');
    Route::post('payroll/gratuity', [\App\Http\Controllers\HR\GratuityController::class, 'store'])->name('payroll.gratuity.store');
    Route::post('payroll/gratuity/{id}/pay', [\App\Http\Controllers\HR\GratuityController::class, 'pay'])->name('payroll.gratuity.pay');
});
