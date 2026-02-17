<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitLetterTemplate;
use App\Models\Legal\LegalTemplate;
use Illuminate\Http\Request;

class ExitLetterTemplateController extends Controller
{
    private function letterTypes(): array
    {
        return [
            'ResignationAcknowledgement',
            'TerminationNotice',
            'RedundancyNotice',
            'ReleaseLetter',
            'CertificateOfService',
            'FinalSettlement',
        ];
    }

    public function index()
    {
        $templates = ExitLetterTemplate::orderBy('LetterType')->get();
        $legalTemplates = LegalTemplate::whereIn('Id', $templates->pluck('TemplateID')->filter())
            ->get(['Id', 'Title'])
            ->keyBy('Id');

        return view('hr.exit.config.letter_templates.index', [
            'templates' => $templates,
            'letterTypes' => $this->letterTypes(),
            'legalTemplates' => $legalTemplates,
        ]);
    }

    public function create()
    {
        $legalTemplates = LegalTemplate::orderBy('Title')->get(['Id', 'Title', 'DocumentType', 'Status']);
        return view('hr.exit.config.letter_templates.create', [
            'legalTemplates' => $legalTemplates,
            'letterTypes' => $this->letterTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'LetterType' => ['required', 'string', 'max:50'],
            'TemplateID' => ['nullable', 'integer', 'exists:t_LegalTemplates,Id'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        ExitLetterTemplate::create([
            'LetterType' => $data['LetterType'],
            'TemplateID' => $data['TemplateID'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-letter-templates.index')->with('success', 'Letter template mapped.');
    }

    public function edit($id)
    {
        $template = ExitLetterTemplate::findOrFail($id);
        $legalTemplates = LegalTemplate::orderBy('Title')->get(['Id', 'Title', 'DocumentType', 'Status']);
        return view('hr.exit.config.letter_templates.edit', [
            'template' => $template,
            'legalTemplates' => $legalTemplates,
            'letterTypes' => $this->letterTypes(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = ExitLetterTemplate::findOrFail($id);
        $data = $request->validate([
            'LetterType' => ['required', 'string', 'max:50'],
            'TemplateID' => ['nullable', 'integer', 'exists:t_LegalTemplates,Id'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $template->update([
            'LetterType' => $data['LetterType'],
            'TemplateID' => $data['TemplateID'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-letter-templates.index')->with('success', 'Letter template updated.');
    }
}
