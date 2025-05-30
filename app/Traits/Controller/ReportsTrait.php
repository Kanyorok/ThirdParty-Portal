<?php

namespace App\Traits\Controller;

use App\Models\Core\Report;
use App\Services\ThirdParty\SSRSService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\DataTables;

trait ReportsTrait
{
    public function getReports(bool $data): View|JsonResponse
    {
        $module = self::Module;
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

    public function show(Report $report): View|RedirectResponse
    {
        if ($report->ModuleId !== self::Module->value) {
            return redirect()->back()->with('fail', 'invalid report.');
        }
        //todo check permissions
        $service = new SSRSService();
        try {
            $xmlResponse = $service->exportReport($report->Path, 'XML', content: true);
        } catch (ConnectionException $e) {
            return redirect()->back()->with('fail', 'cannot connect to the report server.');
        }

        return view('reports.show')
            ->with('report', $report)
            ->with('data', $service->parseReportXml($xmlResponse));
    }


    public function export(Report $report, string $format): StreamedResponse|RedirectResponse
    {
        if ($report->ModuleId !== self::Module->value) {
            return redirect()->back()->with('fail', 'invalid report.');
        }
        //todo check permissions
        $service = new SSRSService();
        try {
            return $service->exportReport($report->Path, $format);
        } catch (ConnectionException $e) {
        }

        return redirect()->back()->with('fail', 'cannot connect to the report server.');
    }
}
