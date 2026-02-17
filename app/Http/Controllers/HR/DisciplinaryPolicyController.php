<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryPolicy;
use Illuminate\Http\Request;

class DisciplinaryPolicyController extends Controller
{
    public function index()
    {
        $policies = DisciplinaryPolicy::orderByDesc('EffectiveFrom')->get();
        return view('hr.discipline.policies.index', compact('policies'));
    }

    public function create()
    {
        return view('hr.discipline.policies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150'],
            'EffectiveFrom' => ['nullable', 'date'],
            'EffectiveTo' => ['nullable', 'date'],
            'EmploymentTypes' => ['nullable', 'string'],
            'ContractTypes' => ['nullable', 'string'],
            'ProgressiveRules' => ['nullable', 'string'],
            'AppealDeadlineDays' => ['required', 'integer', 'min:0'],
            'RetentionMonths' => ['required', 'integer', 'min:0'],
            'AllowDirectHearing' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        DisciplinaryPolicy::create([
            'Name' => $data['Name'],
            'EffectiveFrom' => $data['EffectiveFrom'] ?? null,
            'EffectiveTo' => $data['EffectiveTo'] ?? null,
            'EmploymentTypes' => $data['EmploymentTypes'] ?? null,
            'ContractTypes' => $data['ContractTypes'] ?? null,
            'ProgressiveRules' => $data['ProgressiveRules'] ?? null,
            'AppealDeadlineDays' => $data['AppealDeadlineDays'],
            'RetentionMonths' => $data['RetentionMonths'],
            'AllowDirectHearing' => $request->boolean('AllowDirectHearing', false),
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.policies.index')->with('success', 'Policy saved.');
    }

    public function edit($id)
    {
        $policy = DisciplinaryPolicy::findOrFail($id);
        return view('hr.discipline.policies.edit', compact('policy'));
    }

    public function update(Request $request, $id)
    {
        $policy = DisciplinaryPolicy::findOrFail($id);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150'],
            'EffectiveFrom' => ['nullable', 'date'],
            'EffectiveTo' => ['nullable', 'date'],
            'EmploymentTypes' => ['nullable', 'string'],
            'ContractTypes' => ['nullable', 'string'],
            'ProgressiveRules' => ['nullable', 'string'],
            'AppealDeadlineDays' => ['required', 'integer', 'min:0'],
            'RetentionMonths' => ['required', 'integer', 'min:0'],
            'AllowDirectHearing' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $policy->update([
            'Name' => $data['Name'],
            'EffectiveFrom' => $data['EffectiveFrom'] ?? null,
            'EffectiveTo' => $data['EffectiveTo'] ?? null,
            'EmploymentTypes' => $data['EmploymentTypes'] ?? null,
            'ContractTypes' => $data['ContractTypes'] ?? null,
            'ProgressiveRules' => $data['ProgressiveRules'] ?? null,
            'AppealDeadlineDays' => $data['AppealDeadlineDays'],
            'RetentionMonths' => $data['RetentionMonths'],
            'AllowDirectHearing' => $request->boolean('AllowDirectHearing', false),
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.policies.index')->with('success', 'Policy updated.');
    }
}
