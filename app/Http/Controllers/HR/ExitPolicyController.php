<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitChecklistTemplate;
use App\Models\HR\Exit\ExitPolicy;
use App\Models\HR\Exit\ExitPolicyNoticePeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExitPolicyController extends Controller
{
    public function index()
    {
        $policies = ExitPolicy::with('checklistTemplate')->orderBy('Name')->get();
        return view('hr.exit.config.policies.index', compact('policies'));
    }

    public function create()
    {
        $templates = ExitChecklistTemplate::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        return view('hr.exit.config.policies.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150', 'unique:t_HRExitPolicies,Name'],
            'EffectiveFrom' => ['nullable', 'date'],
            'EffectiveTo' => ['nullable', 'date'],
            'EmploymentTypes' => ['nullable', 'string'],
            'ContractTypes' => ['nullable', 'string'],
            'ApprovalWorkflow' => ['nullable', 'string'],
            'RedundancyCriteria' => ['nullable', 'string'],
            'TerminalDuesConfig' => ['nullable', 'string'],
            'ChecklistTemplateID' => ['nullable', 'integer', 'exists:t_HRExitChecklistTemplates,Id'],
            'AllowNoticePay' => ['sometimes', 'boolean'],
            'AllowNoticeWaiver' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
            'NoticeEmploymentType' => ['nullable', 'array'],
            'NoticeContractType' => ['nullable', 'array'],
            'NoticeDays' => ['nullable', 'array'],
            'NoticePayInLieu' => ['nullable', 'array'],
        ]);

        $policy = ExitPolicy::create([
            'Name' => $data['Name'],
            'EffectiveFrom' => $data['EffectiveFrom'] ?? null,
            'EffectiveTo' => $data['EffectiveTo'] ?? null,
            'EmploymentTypes' => $data['EmploymentTypes'] ?? null,
            'ContractTypes' => $data['ContractTypes'] ?? null,
            'ApprovalWorkflow' => $data['ApprovalWorkflow'] ?? null,
            'RedundancyCriteria' => $data['RedundancyCriteria'] ?? null,
            'TerminalDuesConfig' => $data['TerminalDuesConfig'] ?? null,
            'ChecklistTemplateID' => $data['ChecklistTemplateID'] ?? null,
            'AllowNoticePay' => $request->boolean('AllowNoticePay', true),
            'AllowNoticeWaiver' => $request->boolean('AllowNoticeWaiver', true),
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        $this->syncNoticePeriods($policy, $request);

        return redirect()->route('hr.config.exit-policies.index')->with('success', 'Exit policy saved.');
    }

    public function edit($id)
    {
        $policy = ExitPolicy::with('noticePeriods')->findOrFail($id);
        $templates = ExitChecklistTemplate::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        return view('hr.exit.config.policies.edit', compact('policy', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $policy = ExitPolicy::findOrFail($id);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150', Rule::unique('t_HRExitPolicies', 'Name')->ignore($policy->Id, 'Id')],
            'EffectiveFrom' => ['nullable', 'date'],
            'EffectiveTo' => ['nullable', 'date'],
            'EmploymentTypes' => ['nullable', 'string'],
            'ContractTypes' => ['nullable', 'string'],
            'ApprovalWorkflow' => ['nullable', 'string'],
            'RedundancyCriteria' => ['nullable', 'string'],
            'TerminalDuesConfig' => ['nullable', 'string'],
            'ChecklistTemplateID' => ['nullable', 'integer', 'exists:t_HRExitChecklistTemplates,Id'],
            'AllowNoticePay' => ['sometimes', 'boolean'],
            'AllowNoticeWaiver' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
            'NoticeEmploymentType' => ['nullable', 'array'],
            'NoticeContractType' => ['nullable', 'array'],
            'NoticeDays' => ['nullable', 'array'],
            'NoticePayInLieu' => ['nullable', 'array'],
        ]);

        $policy->update([
            'Name' => $data['Name'],
            'EffectiveFrom' => $data['EffectiveFrom'] ?? null,
            'EffectiveTo' => $data['EffectiveTo'] ?? null,
            'EmploymentTypes' => $data['EmploymentTypes'] ?? null,
            'ContractTypes' => $data['ContractTypes'] ?? null,
            'ApprovalWorkflow' => $data['ApprovalWorkflow'] ?? null,
            'RedundancyCriteria' => $data['RedundancyCriteria'] ?? null,
            'TerminalDuesConfig' => $data['TerminalDuesConfig'] ?? null,
            'ChecklistTemplateID' => $data['ChecklistTemplateID'] ?? null,
            'AllowNoticePay' => $request->boolean('AllowNoticePay', true),
            'AllowNoticeWaiver' => $request->boolean('AllowNoticeWaiver', true),
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        ExitPolicyNoticePeriod::where('PolicyID', $policy->Id)->delete();
        $this->syncNoticePeriods($policy, $request);

        return redirect()->route('hr.config.exit-policies.edit', $policy->Id)->with('success', 'Exit policy updated.');
    }

    private function syncNoticePeriods(ExitPolicy $policy, Request $request): void
    {
        $employmentTypes = $request->input('NoticeEmploymentType', []);
        $contractTypes = $request->input('NoticeContractType', []);
        $noticeDays = $request->input('NoticeDays', []);
        $payInLieu = $request->input('NoticePayInLieu', []);

        foreach ($noticeDays as $index => $days) {
            if ($days === null || $days === '') {
                continue;
            }
            ExitPolicyNoticePeriod::create([
                'PolicyID' => $policy->Id,
                'EmploymentType' => $employmentTypes[$index] ?? null,
                'ContractType' => $contractTypes[$index] ?? null,
                'NoticeDays' => (int)$days,
                'PayInLieuAllowed' => !empty($payInLieu[$index]),
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }
    }
}
