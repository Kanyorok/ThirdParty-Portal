<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiRatingScale;
use App\Models\HR\KpiScoreChart;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiScoreChartController extends Controller
{
    public function index()
    {
        $charts = KpiScoreChart::with('ratingScale')->orderBy('MinPercent')->paginate(30);
        return view('hr.config.kpi.score-charts.index', compact('charts'));
    }

    public function create()
    {
        $scales = KpiRatingScale::orderBy('Name')->get(['Id','Name','MinScore','MaxScore']);
        return view('hr.config.kpi.score-charts.create', compact('scales'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'RatingScaleID' => ['nullable','exists:t_HRKPIRatingScales,Id'],
            'MinPercent' => ['required','numeric','min:0'],
            'MaxPercent' => ['nullable','numeric','min:0'],
            'RatingValue' => ['required','numeric','min:0'],
            'RatingLabel' => ['required','string','max:150'],
            'IsActive' => ['sometimes','boolean'],
        ]);

        if (isset($data['MaxPercent']) && $data['MaxPercent'] < $data['MinPercent']) {
            return back()->withErrors([
                'MaxPercent' => 'Max percent must be greater than or equal to min percent.',
            ])->withInput();
        }

        KpiScoreChart::create([
            'RatingScaleID' => $data['RatingScaleID'] ?? null,
            'MinPercent' => $data['MinPercent'],
            'MaxPercent' => $data['MaxPercent'] ?? null,
            'RatingValue' => $data['RatingValue'],
            'RatingLabel' => $data['RatingLabel'],
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.scorecharts.index')->with('success', 'Score chart saved.');
    }

    public function edit($id)
    {
        $chart = KpiScoreChart::findOrFail($id);
        $scales = KpiRatingScale::orderBy('Name')->get(['Id','Name','MinScore','MaxScore']);
        return view('hr.config.kpi.score-charts.edit', compact('chart','scales'));
    }

    public function update(Request $request, $id)
    {
        $chart = KpiScoreChart::findOrFail($id);
        $data = $request->validate([
            'RatingScaleID' => ['nullable','exists:t_HRKPIRatingScales,Id'],
            'MinPercent' => ['required','numeric','min:0'],
            'MaxPercent' => ['nullable','numeric','min:0'],
            'RatingValue' => ['required','numeric','min:0'],
            'RatingLabel' => ['required','string','max:150'],
            'IsActive' => ['sometimes','boolean'],
        ]);

        if (isset($data['MaxPercent']) && $data['MaxPercent'] < $data['MinPercent']) {
            return back()->withErrors([
                'MaxPercent' => 'Max percent must be greater than or equal to min percent.',
            ])->withInput();
        }

        $chart->update([
            'RatingScaleID' => $data['RatingScaleID'] ?? null,
            'MinPercent' => $data['MinPercent'],
            'MaxPercent' => $data['MaxPercent'] ?? null,
            'RatingValue' => $data['RatingValue'],
            'RatingLabel' => $data['RatingLabel'],
            'IsActive' => $request->boolean('IsActive', false),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.scorecharts.index')->with('success', 'Score chart updated.');
    }

    public function destroy($id)
    {
        $chart = KpiScoreChart::findOrFail($id);
        $chart->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.scorecharts.index')->with('success', 'Score chart deactivated.');
    }
}
