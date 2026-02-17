<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitChecklistItem;
use App\Models\HR\Exit\ExitChecklistTemplate;
use App\Models\HR\Exit\ExitClearanceDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExitChecklistTemplateController extends Controller
{
    public function index()
    {
        $templates = ExitChecklistTemplate::orderBy('Name')->get();
        return view('hr.exit.config.checklists.index', compact('templates'));
    }

    public function create()
    {
        return view('hr.exit.config.checklists.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150', 'unique:t_HRExitChecklistTemplates,Name'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        ExitChecklistTemplate::create([
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-checklists.index')->with('success', 'Checklist template saved.');
    }

    public function edit($id)
    {
        ExitClearanceDepartment::syncFromDepartments(auth()->id());
        $template = ExitChecklistTemplate::findOrFail($id);
        $items = ExitChecklistItem::where('TemplateID', $template->Id)
            ->whereNull('DeletedOn')
            ->orderBy('Sequence')
            ->get();
        $departments = ExitClearanceDepartment::where('IsActive', 1)->orderBy('Sequence')->get(['Id', 'Name']);

        return view('hr.exit.config.checklists.edit', compact('template', 'items', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $template = ExitChecklistTemplate::findOrFail($id);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150', Rule::unique('t_HRExitChecklistTemplates', 'Name')->ignore($template->Id, 'Id')],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $template->update([
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-checklists.edit', $template->Id)->with('success', 'Checklist template updated.');
    }

    public function addItem(Request $request, $id)
    {
        ExitClearanceDepartment::syncFromDepartments(auth()->id());
        $template = ExitChecklistTemplate::findOrFail($id);
        $data = $request->validate([
            'ItemName' => ['required', 'string', 'max:150'],
            'ClearanceDepartmentID' => ['nullable', 'integer', 'exists:t_HRExitClearanceDepartments,Id'],
            'Sequence' => ['nullable', 'integer', 'min:1'],
            'IsMandatory' => ['sometimes', 'boolean'],
        ]);

        ExitChecklistItem::create([
            'TemplateID' => $template->Id,
            'ClearanceDepartmentID' => $data['ClearanceDepartmentID'] ?? null,
            'ItemName' => $data['ItemName'],
            'Sequence' => $data['Sequence'] ?? 1,
            'IsMandatory' => $request->boolean('IsMandatory', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-checklists.edit', $template->Id)->with('success', 'Checklist item added.');
    }

    public function removeItem($id, $itemId)
    {
        $template = ExitChecklistTemplate::findOrFail($id);
        $item = ExitChecklistItem::where('TemplateID', $template->Id)
            ->where('Id', $itemId)
            ->whereNull('DeletedOn')
            ->firstOrFail();

        $item->update([
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-checklists.edit', $template->Id)->with('success', 'Checklist item removed.');
    }
}
