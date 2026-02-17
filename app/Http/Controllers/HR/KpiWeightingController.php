<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HR\KpiWeightingRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiWeightingController extends Controller
{
    public function index()
    {
        $rules = KpiWeightingRule::orderBy('Name')->paginate(20);

        return view('hr.config.kpi.weighting.index', compact('rules'));
    }

    public function create()
    {
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.config.kpi.weighting.create', compact('grades', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRKPIWeightingRules,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'GradeID' => ['nullable', 'integer'],
            'RoleID' => ['nullable', 'integer'],
            'TotalWeight' => ['required', 'integer', 'min:0', 'max:100'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiWeightingRule::create($data);

        return redirect()->route('hr.config.kpi.weighting.index')->with('success', 'Weighting rule created.');
    }

    public function edit($id)
    {
        $rule = KpiWeightingRule::findOrFail($id);
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.config.kpi.weighting.edit', compact('rule', 'grades', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $rule = KpiWeightingRule::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRKPIWeightingRules', 'Code')->ignore($rule->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'GradeID' => ['nullable', 'integer'],
            'RoleID' => ['nullable', 'integer'],
            'TotalWeight' => ['required', 'integer', 'min:0', 'max:100'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $rule->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $rule->update($data);

        return redirect()->route('hr.config.kpi.weighting.index')->with('success', 'Weighting rule updated.');
    }

    public function destroy($id)
    {
        $rule = KpiWeightingRule::findOrFail($id);
        $rule->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.weighting.index')->with('success', 'Weighting rule deactivated.');
    }
}
