<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HR\KpiPeriod;
use App\Models\HR\KpiPerspective;
use App\Models\HR\KpiPerspectiveWeight;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KpiPerspectiveWeightController extends Controller
{
    public function index()
    {
        $weights = KpiPerspectiveWeight::with(['perspective', 'period', 'grade', 'role'])
            ->orderBy('PerspectiveID')
            ->orderBy('PeriodID')
            ->paginate(20);

        return view('hr.config.kpi.perspective-weights.index', compact('weights'));
    }

    public function create()
    {
        $perspectives = KpiPerspective::where('IsActive', 1)->orderBy('Name')->get();
        $periods = KpiPeriod::where('IsActive', 1)->orderBy('Name')->get();
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.config.kpi.perspective-weights.create', compact('perspectives', 'periods', 'grades', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'PerspectiveID' => ['required', 'integer', 'exists:t_HRKPIPerspectives,Id'],
            'PeriodID' => ['required', 'integer', 'exists:t_HRKPIPeriods,Id'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'RoleID' => ['nullable', 'integer', 'exists:t_Roles,id'],
            'Weight' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        $exists = KpiPerspectiveWeight::where('PerspectiveID', $data['PerspectiveID'])
            ->where('PeriodID', $data['PeriodID'])
            ->where('GradeID', $data['GradeID'])
            ->where('RoleID', $data['RoleID'])
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'PerspectiveID' => 'A weight already exists for this perspective, period, grade, and role.',
            ]);
        }

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiPerspectiveWeight::create($data);

        return redirect()->route('hr.config.kpi.perspective-weights.index')->with('success', 'Perspective weight created.');
    }

    public function edit($id)
    {
        $weight = KpiPerspectiveWeight::findOrFail($id);
        $perspectives = KpiPerspective::where('IsActive', 1)->orderBy('Name')->get();
        $periods = KpiPeriod::where('IsActive', 1)->orderBy('Name')->get();
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.config.kpi.perspective-weights.edit', compact('weight', 'perspectives', 'periods', 'grades', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $weight = KpiPerspectiveWeight::findOrFail($id);
        $data = $request->validate([
            'PerspectiveID' => ['required', 'integer', 'exists:t_HRKPIPerspectives,Id'],
            'PeriodID' => ['required', 'integer', 'exists:t_HRKPIPeriods,Id'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'RoleID' => ['nullable', 'integer', 'exists:t_Roles,id'],
            'Weight' => ['required', 'numeric', 'min:0', 'max:1'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $exists = KpiPerspectiveWeight::where('PerspectiveID', $data['PerspectiveID'])
            ->where('PeriodID', $data['PeriodID'])
            ->where('GradeID', $data['GradeID'])
            ->where('RoleID', $data['RoleID'])
            ->where('Id', '<>', $weight->Id)
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'PerspectiveID' => 'A weight already exists for this perspective, period, grade, and role.',
            ]);
        }

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $weight->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $weight->update($data);

        return redirect()->route('hr.config.kpi.perspective-weights.index')->with('success', 'Perspective weight updated.');
    }

    public function destroy($id)
    {
        $weight = KpiPerspectiveWeight::findOrFail($id);
        $weight->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.perspective-weights.index')->with('success', 'Perspective weight deactivated.');
    }
}
