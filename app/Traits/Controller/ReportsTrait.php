<?php

namespace App\Traits\Controller;

use App\Exceptions\ErroredException;
use App\Models\Core\Report;
use App\Services\ThirdParty\SSRSService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Yajra\DataTables\DataTables;

trait ReportsTrait
{
    public function getReports(bool $data): View|JsonResponse
    {
        $module = self::MODULE;
        if ($data) {
            try {
                return Datatables::of(Report::query()->where('t_Reports.ModuleId', $module->value)->select('*'))->addIndexColumn()
                    ->addColumn('action', function (Report $report) use ($module) {
                        return '<a href="' . route(Str::lower($module->name) . '-reports.show', [$report->Id]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> view</a>';
                    })->editColumn('Name', function (Report $report) use ($module) {
                        return '<a href="' . route(Str::lower($module->name) . '-reports.show', [$report->Id]) . '" class="">' . $report->Name . '</a>';
                    })->rawColumns(['action', 'Name'])->make();
            } catch (Exception) {
            }
            return $this->errored('cannot retrieve ' . Str::lower($module->description()) . ' reports.');
        }

        return view('reports.index')->with('module', $module);
    }


    public function show(Request $request, Report $report): View|RedirectResponse
    {
        if ($report->ModuleId !== self::Module->value) {
            return redirect()->back()->with('fail', 'invalid report.');
        }
        //todo check permissions

        if ($request->ajax()) {
            try {
                $service = new SSRSService();
                $ssrsReport = $service->getReportByPath($report->Path);
                if (!array_key_exists('Type', $ssrsReport) || $ssrsReport['Type'] !== "Report") {
                    throw new ErroredException('invalid report.');
                }

                $parameters = $service->getReportParametersValidated($ssrsReport['Id'], $request->all());
                $xmlResponse = $service->exportReport($report->Path, parameters: $parameters, format: 'XML', content: true);
                if (!str_contains($xmlResponse, 'xml')) {
                    throw new ErroredException('invalid report.');
                }
                $data = $service->parseReportXml($xmlResponse);
            } catch (ConnectionException $e) {
                return view('snippets.errors')->with('message', 'cannot connect to the report server.');
            } catch (ErroredException $e) {
                return view('snippets.errors')->with('message', $e->getMessage() ?? 'cannot retrieve report data.');
            } catch (Throwable|Exception $e) {
                return view('snippets.errors')->with('message', 'cannot retrieve report data.');
            }

            return ($data->isEmpty())
                ? view('snippets.errors')->with('message', 'Report has no data. Please check your report parameters and try again..')
                : view('reports.table', compact('report', 'data'))->with('params', SSRSService::queryParams(collect($parameters)->put('_key', md5($report->Path))->toArray()));

        }

        try {
            $service = new SSRSService();
            $ssrsReport = $service->getReportByPath($report->Path);
            if (!array_key_exists('Type', $ssrsReport) || $ssrsReport['Type'] !== "Report") {
                throw new ErroredException('invalid report.');
            }
            $parameters = (array_key_exists('HasParameters', $ssrsReport) && $ssrsReport['HasParameters'] === true) ?
                $service->getReportParameters($ssrsReport['Id']) : [];
        } catch (ConnectionException) {
            return redirect()->back()->with('fail', 'cannot connect to the report server.');
        } catch (ErroredException $e) {
            return redirect()->back()->with('fail', $e->getMessage() ?? 'cannot retrieve report data.');
        } catch (Throwable|Exception $e) {
            return redirect()->back()->with('fail', 'cannot retrieve report data.');
        }

        return view('reports.show', compact('report', 'parameters'));
    }

    public function export(Request $request, Report $report, string $format): StreamedResponse|RedirectResponse
    {
        if ($report->ModuleId !== self::Module->value) {
            return redirect()->back()->with('fail', 'invalid report.');
        }
        //todo check permissions

        if (!$request->has('_key') || md5($report->Path) !== $request->get('_key')) {
            return redirect()->back()->with('fail', 'download link expired. please refresh the report page and try again..');
        }

        try {
            $service = new SSRSService();
            $ssrsReport = $service->getReportByPath($report->Path);
            return $service->exportReport($report->Path, parameters: $service->getReportParametersValidated($ssrsReport['Id'], $request->all()), format: $format);
        } catch (ConnectionException $e) {
            return redirect()->back()->with('fail', 'cannot connect to the report server.');
        } catch (ErroredException $e) {
            return redirect()->back()->with('fail', $e->getMessage() ?? 'cannot retrieve report data.');
        } catch (Throwable|Exception $e) {
            return redirect()->back()->with('fail', 'cannot retrieve report data.');
        }
    }
}
