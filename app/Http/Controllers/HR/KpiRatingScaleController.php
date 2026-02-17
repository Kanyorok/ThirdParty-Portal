<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiRatingScale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiRatingScaleController extends Controller
{
    public function index()
    {
        $scales = KpiRatingScale::orderBy('Name')->paginate(20);

        return view('hr.config.kpi.rating-scales.index', compact('scales'));
    }

    public function create()
    {
        return view('hr.config.kpi.rating-scales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRKPIRatingScales,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'MinScore' => ['required', 'numeric'],
            'MaxScore' => ['required', 'numeric', 'gt:MinScore'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiRatingScale::create($data);

        return redirect()->route('hr.config.kpi.ratingscales.index')->with('success', 'Rating scale created.');
    }

    public function edit($id)
    {
        $scale = KpiRatingScale::findOrFail($id);

        return view('hr.config.kpi.rating-scales.edit', compact('scale'));
    }

    public function update(Request $request, $id)
    {
        $scale = KpiRatingScale::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRKPIRatingScales', 'Code')->ignore($scale->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'MinScore' => ['required', 'numeric'],
            'MaxScore' => ['required', 'numeric', 'gt:MinScore'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $scale->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $scale->update($data);

        return redirect()->route('hr.config.kpi.ratingscales.index')->with('success', 'Rating scale updated.');
    }

    public function destroy($id)
    {
        $scale = KpiRatingScale::findOrFail($id);
        $scale->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.ratingscales.index')->with('success', 'Rating scale deactivated.');
    }
}
