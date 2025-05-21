<?php

namespace App\Traits\Controller;

use App\Enums\Core\ModulesEnum;
use App\Models\Core\Report;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

trait ReportsTrait
{
    public function getReports(bool $data, ModulesEnum $module): View|JsonResponse
    {
        if ($data) {
            try {
                return Datatables::of(Report::query()->where('t_Reports.ModuleId', $module->value)->select('*'))->addIndexColumn()
                    ->addColumn('action', function (Report $report) use ($module) {
                        return '<a href="' . route(Str::lower($module->name) . '-reports.show', [$report->Id]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> view</a>';
                    })->make();
            } catch (Exception) {
            }
            return $this->errored('cannot retrieve inventory reports.');
        }

        return view('reports.index')->with('module', $module);
    }

    public function show(Report $report): RedirectResponse|View
    {

        return view('reports.show')->with('report', $report);
    }
}
