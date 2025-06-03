<?php

namespace App\Http\Controllers\CRM;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Traits\Controller\ReportsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController extends Controller
{
    protected const ModulesEnum Module = ModulesEnum::CRM;

    use ReportsTrait;

    public function index(Request $request): JsonResponse|View
    {
        return $this->getReports($request->ajax());
    }
}
