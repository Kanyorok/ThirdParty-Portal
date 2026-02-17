<?php

namespace App\Http\Controllers\HR;

use App\Exports\HR\BulkAllowancesTemplateExport;
use App\Exports\HR\BulkAttendanceTemplateExport;
use App\Exports\HR\BulkDeductionsTemplateExport;
use App\Exports\HR\BulkEmployeesTemplateExport;
use App\Exports\HR\BulkKpiTargetsTemplateExport;
use App\Exports\HR\BulkSalaryTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\HR\BulkAllowancesImport;
use App\Imports\HR\BulkAttendanceImport;
use App\Imports\HR\BulkDeductionsImport;
use App\Imports\HR\BulkEmployeesImport;
use App\Imports\HR\BulkKpiTargetsImport;
use App\Imports\HR\BulkSalaryImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BulkUploadController extends Controller
{
    public function index()
    {
        return view('hr.bulk.index');
    }

    public function employees()
    {
        return view('hr.bulk.employees');
    }

    public function importEmployees(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new BulkEmployeesImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Processed %d rows. Created %d, updated %d, skipped %d.',
            $import->getProcessedCount(),
            $import->getCreatedCount(),
            $import->getUpdatedCount(),
            $import->getSkippedCount()
        ));
    }

    public function downloadEmployeesTemplate()
    {
        return Excel::download(new BulkEmployeesTemplateExport(), 'hr_bulk_employees_template.xlsx');
    }

    public function payroll()
    {
        return view('hr.bulk.payroll');
    }

    public function importSalary(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new BulkSalaryImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Processed %d rows. Updated %d, skipped %d.',
            $import->getProcessedCount(),
            $import->getUpdatedCount(),
            $import->getSkippedCount()
        ));
    }

    public function downloadSalaryTemplate()
    {
        return Excel::download(new BulkSalaryTemplateExport(), 'hr_bulk_salary_template.xlsx');
    }

    public function importAllowances(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new BulkAllowancesImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Processed %d rows. Created %d, updated %d, skipped %d.',
            $import->getProcessedCount(),
            $import->getCreatedCount(),
            $import->getUpdatedCount(),
            $import->getSkippedCount()
        ));
    }

    public function downloadAllowancesTemplate()
    {
        return Excel::download(new BulkAllowancesTemplateExport(), 'hr_bulk_allowances_template.xlsx');
    }

    public function importDeductions(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new BulkDeductionsImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Processed %d rows. Created %d, updated %d, skipped %d.',
            $import->getProcessedCount(),
            $import->getCreatedCount(),
            $import->getUpdatedCount(),
            $import->getSkippedCount()
        ));
    }

    public function downloadDeductionsTemplate()
    {
        return Excel::download(new BulkDeductionsTemplateExport(), 'hr_bulk_deductions_template.xlsx');
    }

    public function attendance()
    {
        return view('hr.bulk.attendance');
    }

    public function importAttendance(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new BulkAttendanceImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Processed %d rows. Created %d, skipped %d.',
            $import->getProcessedCount(),
            $import->getCreatedCount(),
            $import->getSkippedCount()
        ));
    }

    public function downloadAttendanceTemplate()
    {
        return Excel::download(new BulkAttendanceTemplateExport(), 'hr_bulk_attendance_template.xlsx');
    }

    public function kpiTargets()
    {
        return view('hr.bulk.kpi-targets');
    }

    public function importKpiTargets(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new BulkKpiTargetsImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Processed %d rows. Created %d, updated %d, skipped %d.',
            $import->getProcessedCount(),
            $import->getCreatedCount(),
            $import->getUpdatedCount(),
            $import->getSkippedCount()
        ));
    }

    public function downloadKpiTargetsTemplate()
    {
        return Excel::download(new BulkKpiTargetsTemplateExport(), 'hr_bulk_kpi_targets_template.xlsx');
    }
}
