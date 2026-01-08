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
use Illuminate\Support\Facades\Log;
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
                return Datatables::of(Report::query()->accessibleToUser(auth()->user())->where('t_Reports.ModuleId', $module->value)->select('*'))->addIndexColumn()
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
        if ($report->ModuleId !== self::MODULE->value) {
            return redirect()->back()->with('fail', 'invalid report.');
        }
        if ($report->PermissionName && !auth()->user()->hasPermissionTo($report->PermissionName)) {
            return redirect()->back()->with('fail', 'unauthorized access.');
        }

        if ($request->ajax()) {
            try {
                $service = new SSRSService();
                $ssrsReport = $service->getReportByPath($report->Path);
                if (!array_key_exists('Type', $ssrsReport) || $ssrsReport['Type'] !== "Report") {
                    throw new ErroredException('invalid report.');
                }

                $parameters = $service->getReportParametersValidated($ssrsReport['Id'], $request->all());

                $xmlResponse = $service->exportReport($report->Path, parameters: $parameters->toArray(), content: true);
                if (!str_contains($xmlResponse, 'xml')) {
                    throw new ErroredException('invalid report.');
                }
                $data = $service->parseReportXml($xmlResponse);
            } catch (ConnectionException | ErroredException $e) {
                Log::error('Error Load Report :');
                Log::error($e);
                try {
                    $service = new SSRSService();
                    $ssrsReport = $service->getReportByPath($report->Path);
                    $parameters = $service->getReportParametersValidated($ssrsReport['Id'], $request->all());
                } catch (ErroredException $e) {
                    Log::error('Error Load Report :');
                    Log::error($e);
                    return view('snippets.errors')->with('message', 'cannot connect to the report server.');
                } catch (ConnectionException $e) {
                    return view('snippets.errors')->with('message', 'cannot reach the report server.');
                }
                return view('reports.table', compact('report'))->with('data', collect())->with('params', SSRSService::queryParams($parameters->put('_key', md5($report->Path))->toArray()))
                    ->with('module', Str::lower(self::MODULE->name))->with('message', 'Cannot generate preview, try export');
            } catch (Throwable | Exception $e) {
                Log::error('Error Load Report :');
                Log::error($e);
                return view('snippets.errors')->with('message', 'cannot retrieve report data.');
            }

            return view('reports.table', compact('report', 'data'))->with('params', SSRSService::queryParams($parameters->put('_key', md5($report->Path))->toArray()))
                ->with('module', Str::lower(self::MODULE->name))->with('message', 'Report has no data. Please check your report parameters and try again.');
        }

        // try {
        $service = new SSRSService();
        $ssrsReport = $service->getReportByPath($report->Path);
        if (!array_key_exists('Type', $ssrsReport) || $ssrsReport['Type'] !== "Report") {
            throw new ErroredException('invalid report.');
        }
        $parameters = (array_key_exists('HasParameters', $ssrsReport) && $ssrsReport['HasParameters'] === true) ?
            $service->getReportParameters($ssrsReport['Id']) : [];
        /*} catch (ConnectionException) {
            return redirect()->back()->with('fail', 'cannot connect to the report server.');
        } catch (ErroredException $e) {
            return redirect()->back()->with('fail', $e->getMessage() ?? 'cannot retrieve report data.');
        } catch (Throwable|Exception $e) {
            if ($e->getCode() === 404) {
                return redirect()->back()->with('fail', 'report not found.');
            }
            return redirect()->back()->with('fail', 'cannot retrieve report data.');
        }*/

        return view('reports.show', compact('report', 'parameters'));
    }

    public function export(Request $request, Report $report, string $format): StreamedResponse|RedirectResponse
    {
        if ($report->PermissionName && !auth()->user()->hasPermissionTo($report->PermissionName)) {
            return redirect()->back()->with('fail', 'unauthorized access.');
        }

        if ((int)$report->ModuleId !== self::MODULE->value) {
            return redirect()->back()->with('fail', 'invalid report.');
        }

        if (!$request->has('_key') || md5($report->Path) !== $request->get('_key')) {
            return redirect()->back()->with('fail', 'download link expired. please refresh the report page and try again..');
        }

        try {
            $service = new SSRSService();
            $ssrsReport = $service->getReportByPath($report->Path);
            return $service->exportReport($report->Path, parameters: $service->getReportParametersValidated($ssrsReport['Id'], $request->all())->toArray(), format: $format);
        } catch (ConnectionException $e) {
            return redirect()->back()->with('fail', 'cannot connect to the report server.');
        } catch (ErroredException $e) {
            return redirect()->back()->with('fail', $e->getMessage() ?? 'cannot retrieve report data.');
        } catch (Throwable | Exception $e) {
            return redirect()->back()->with('fail', 'cannot retrieve report data.');
        }
    }
}
