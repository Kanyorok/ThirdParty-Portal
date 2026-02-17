<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobGrade;
use App\Models\HR\OvertimeRate;
use Illuminate\Http\Request;

class OvertimeRateController extends Controller
{
    public function index()
    {
        $rates = OvertimeRate::with('grade')->orderBy('GradeID')->paginate(20);

        return view('hr.attendance.overtime-rates.index', compact('rates'));
    }

    public function create()
    {
        $grades = JobGrade::orderBy('Name')->get(['Id', 'Name']);

        return view('hr.attendance.overtime-rates.create', compact('grades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'GradeID' => ['required', 'exists:t_HRJobGrades,Id'],
            'RateMultiplier' => ['required', 'numeric', 'min:0'],
            'EffectiveFrom' => ['nullable', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : true;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        if ($data['IsActive']) {
            OvertimeRate::where('GradeID', $data['GradeID'])->update(['IsActive' => 0]);
        }

        OvertimeRate::create($data);

        return redirect()->route('hr.attendance.overtime-rates.index')
            ->with('success', 'Overtime rate saved.');
    }

    public function edit($id)
    {
        $rate = OvertimeRate::findOrFail($id);
        $grades = JobGrade::orderBy('Name')->get(['Id', 'Name']);

        return view('hr.attendance.overtime-rates.edit', compact('rate', 'grades'));
    }

    public function update(Request $request, $id)
    {
        $rate = OvertimeRate::findOrFail($id);

        $data = $request->validate([
            'GradeID' => ['required', 'exists:t_HRJobGrades,Id'],
            'RateMultiplier' => ['required', 'numeric', 'min:0'],
            'EffectiveFrom' => ['nullable', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $rate->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        if ($data['IsActive']) {
            OvertimeRate::where('GradeID', $data['GradeID'])
                ->where('Id', '!=', $rate->Id)
                ->update(['IsActive' => 0]);
        }

        $rate->update($data);

        return redirect()->route('hr.attendance.overtime-rates.index')
            ->with('success', 'Overtime rate updated.');
    }

    public function destroy($id)
    {
        $rate = OvertimeRate::findOrFail($id);
        $rate->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.attendance.overtime-rates.index')
            ->with('success', 'Overtime rate deactivated.');
    }
}
