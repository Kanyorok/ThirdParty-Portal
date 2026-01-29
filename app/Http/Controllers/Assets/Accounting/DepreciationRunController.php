<?php

namespace App\Http\Controllers\Assets\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Assets\Accounting\{DepreciationRun, DepreciationRunLine};
use App\Models\Assets\Master\{AssetBookValue, AssetHistory};
use App\Models\Assets\Settings\AssetBook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepreciationRunController extends Controller
{
    public function index(Request $request)
    {
        $bookId = $request->get('book');
        $rows = DepreciationRun::when($bookId, fn ($q) => $q->where('BookID', $bookId))
                 ->orderByDesc('CreatedOn')->paginate(20);
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.acc.depruns.index', compact('rows', 'books', 'bookId'));
    }

    public function create()
    {
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.acc.depruns.create', compact('books'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'BookID' => 'required|integer|exists:t_AssetBooks,Id',
            'PeriodStart' => 'required|date',
            'PeriodEnd' => 'required|date|after_or_equal:PeriodStart',
            'Remarks' => 'nullable|max:250',
        ]);

        $run = null;
        DB::transaction(function () use (&$run, $data) {
            $run = DepreciationRun::create($data + ['Status' => 'Draft']);

            // gather ABVs for book
            $abvs = AssetBookValue::where('BookID', $data['BookID'])
                    ->where('IsActive', 1)
                    ->get();

            $ps = Carbon::parse($data['PeriodStart']);
            $pe = Carbon::parse($data['PeriodEnd']);
            foreach ($abvs as $abv) {
                // eligible window
                $depStart = $abv->DepStartDate ? Carbon::parse($abv->DepStartDate) : null;
                if ($depStart && $depStart->gt($pe)) {
                    continue;
                }

                $months = 0;
                $start = $depStart && $depStart->gt($ps) ? $depStart : $ps;
                $months = ($start->year * 12 + $start->month) <= ($pe->year * 12 + $pe->month)
                          ? (($pe->year - $start->year) * 12 + ($pe->month - $start->month) + 1)
                          : 0;

                if ($months <= 0) {
                    continue;
                }

                $opening = (float)$abv->NBV;
                if ($opening <= 0.0) {
                    continue;
                }

                $cost = (float)$abv->AcquisitionCost;
                $lifeM = (int)($abv->UsefulLifeMonths ?? 0);
                $resPct = (float)($abv->ResidualPct ?? 0);
                $resVal = max(0.0, $cost * $resPct / 100.0);
                $method = $abv->DepMethod ?? 'SL';

                $dep = 0.0;
                if ($method === 'SL' && $lifeM > 0) {
                    $monthly = max(0.0, ($cost - $resVal) / $lifeM);
                    $dep = $monthly * $months;
                } elseif ($method === 'DB' && $lifeM > 0) {
                    $annualRate = 2.0 / max(1.0, $lifeM / 12.0);
                    $monthlyRate = $annualRate / 12.0;
                    $dep = $opening * (1 - pow(1 - $monthlyRate, $months));
                } else {
                    // Unsupported method -> skip
                    continue;
                }

                // floor not below residual
                $minNBV = max(0.0, $resVal);
                $dep = min($dep, max(0.0, $opening - $minNBV));
                $closing = $opening - $dep;

                DepreciationRunLine::create([
                    'RunID' => $run->Id,'AssetID' => $abv->AssetID,'BookID' => $abv->BookID,
                    'MethodUsed' => $method,'OpeningNBV' => $opening,'DepAmount' => round($dep, 2),
                    'ClosingNBV' => round($closing, 2),'Months' => $months,'Notes' => null,
                ]);
            }
        });

        return redirect()->route('assets.acc.depruns.show', $run->Id)
               ->with('success', 'Draft depreciation run created.');
    }

    public function show(int $id)
    {
        $run = DepreciationRun::findOrFail($id);
        $lines = DepreciationRunLine::where('RunID', $id)->orderBy('AssetID')->paginate(50);

        return view('assets.acc.depruns.show', compact('run', 'lines'));
    }

    public function post(int $id)
    {
        $run = DepreciationRun::findOrFail($id);
        if ($run->Status !== 'Draft') {
            return back()->withErrors('Only Draft can be posted.');
        }

        DB::transaction(function () use ($run) {
            $lines = DepreciationRunLine::where('RunID', $run->Id)->get();
            foreach ($lines as $L) {
                $abv = AssetBookValue::where('AssetID', $L->AssetID)->where('BookID', $L->BookID)->first();
                if (! $abv) {
                    continue;
                }
                $abv->AccumDep = round((float)$abv->AccumDep + (float)$L->DepAmount, 2);
                $abv->NBV = round((float)$L->ClosingNBV, 2);
                $abv->LastDepRunDate = $run->PeriodEnd;
                $abv->save();

                AssetHistory::create([
                    'AssetID' => $L->AssetID,'EventType' => 'DepRun','EventDate' => $run->PeriodEnd,
                    'Reference' => 'DEP-' . $run->Id,'Remarks' => "Depreciation posted (Book {$L->BookID})",'CreatedOn' => now(),
                ]);
            }
            $run->Status = 'Posted';
            $run->PostedOn = now();
            $run->save();
        });

        return back()->with('success', 'Depreciation posted.');
    }
}
