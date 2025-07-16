<?php

namespace App\Http\Controllers\Auth;

use App\Enums\LeadStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetGLMaster;
use App\Models\CRM\Lead;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected const MONTHS = 6;
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return View
     */
    public function __invoke(Request $request): View
    {
        $actor = $request->user();
        $data = [
                 'leads'     => [
                                 'line'  => [
                                             'labels'    => [],
                                             'converted' => [],
                                            ],
                                 'donut' => ['labels' => []],
                                 'total' => 0,
                                ],
                 'campaigns' => [
                                 'active' => 0,
                                 'sent'   => 0,
                                ],
                 'schedule'  => [
                                 'calls'        => 0,
                                 'appointments' => 0,
                                 'total'        => 0,
                                ],
                 'tickets'   => ['active' => 0],
                ];

        //Fetch Number of open budgets, Total GLS
        $openBudgets=Budget::where('Status','draft')->count();
        $totalGLS=BudgetGLMaster::count();

        //Fetch Number of documents and total file size

        return view('auth.dashboard', compact(
            'data',
            'openBudgets',
            'totalGLS'
        ));
    }

    protected function months(): array
    {
        $dates = collect();
        $dateTime = Carbon::now()->startOfMonth()->subMonths(self::MONTHS);
        for ($i = 1; $i <= self::MONTHS; $i++) {
            $dateTime->addMonth();
            $dates->add($dateTime->format('M y'));
        }
        return $dates->toArray();
    }

    private function converted(User $actor): array
    {
        $data = collect();
        $dateTime = Carbon::now()->startOfMonth()->subMonths(self::MONTHS);
        for ($i = 1; $i <= self::MONTHS; $i++) {
            $dateTime->addMonth();
            $converted = 0;
            try {
                $converted = Lead::withTrashed()->whereBetween('DeletedOn', [$dateTime->copy()->startOfMonth(), $dateTime->copy()->endOfMonth()])
                    ->where('Status', LeadStatusEnum::Won->value)->where('RelationshipManagerID', $actor->Id)->count();
            } catch (\Exception) {
            }
            $data->add($converted);
        }

        return $data->toArray();
    }
}
