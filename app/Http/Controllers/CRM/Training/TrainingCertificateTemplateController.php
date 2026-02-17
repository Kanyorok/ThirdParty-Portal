<?php

namespace App\Http\Controllers\CRM\Training;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\CRM\Training\TrainingCertificateTemplate;
use App\Models\CRM\Training\TrainingProgram;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingCertificateTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingCertificateTemplate::with('program');

        if ($request->filled('program_id')) {
            $query->where('ProgramID', (int) $request->program_id);
        }

        if ($request->filled('scope')) {
            if ($request->scope === 'global') {
                $query->whereNull('ProgramID');
            } elseif ($request->scope === 'program') {
                $query->whereNotNull('ProgramID');
            }
        }

        if ($request->filled('active')) {
            $query->where('IsActive', $request->active === '1' ? 1 : 0);
        }

        if ($request->filled('sample')) {
            $query->where('IsSample', $request->sample === '1' ? 1 : 0);
        }

        $templates = $query->orderByDesc('IsActive')
            ->orderByDesc('IsSample')
            ->orderBy('Name')
            ->paginate(30)
            ->withQueryString();

        $programs = TrainingProgram::orderBy('Title')->get(['Id', 'Title']);
        $scopeList = [
            'global' => 'Global',
            'program' => 'Program-specific',
        ];

        return view('crm.training.certificate-templates.index', compact('templates', 'programs', 'scopeList'));
    }

    public function create()
    {
        $programs = TrainingProgram::orderBy('Title')->get(['Id', 'Title']);
        $placeholderTokens = $this->placeholderTokens();
        $backgroundImageChoices = $this->backgroundImageChoices();

        return view('crm.training.certificate-templates.create', compact('programs', 'placeholderTokens', 'backgroundImageChoices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ProgramID' => ['nullable', 'exists:t_CRMTrainingPrograms,Id'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'TemplateBody' => ['nullable', 'string'],
            'BackgroundImagePath' => ['nullable', 'string', 'max:255', Rule::in(array_keys($this->backgroundImageChoices()))],
            'DefaultIssuingBody' => ['nullable', 'string', 'max:150'],
            'DefaultValidityMonths' => ['nullable', 'integer', 'min:1', 'max:240'],
            'TemplateDocumentFile' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,svg'],
            'IsSample' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $documentId = null;
        if ($request->hasFile('TemplateDocumentFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::CRM),
                $request->file('TemplateDocumentFile'),
                auth()->user()
            )->document;
            $documentId = $document->Id;
        }

        TrainingCertificateTemplate::create([
            'ProgramID' => ! empty($data['ProgramID']) ? (int) $data['ProgramID'] : null,
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'TemplateBody' => $data['TemplateBody'] ?? null,
            'BackgroundImagePath' => $data['BackgroundImagePath'] ?? null,
            'DefaultIssuingBody' => $data['DefaultIssuingBody'] ?? null,
            'DefaultValidityMonths' => $data['DefaultValidityMonths'] ?? null,
            'TemplateDocumentId' => $documentId,
            'IsSample' => $request->boolean('IsSample', false),
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('crm.training.certificate-templates.index')
            ->with('success', 'Certificate template created.');
    }

    public function edit($id)
    {
        $template = TrainingCertificateTemplate::findOrFail($id);
        $programs = TrainingProgram::orderBy('Title')->get(['Id', 'Title']);
        $placeholderTokens = $this->placeholderTokens();
        $backgroundImageChoices = $this->backgroundImageChoices();

        return view('crm.training.certificate-templates.edit', compact('template', 'programs', 'placeholderTokens', 'backgroundImageChoices'));
    }

    public function update(Request $request, $id)
    {
        $template = TrainingCertificateTemplate::findOrFail($id);

        $data = $request->validate([
            'ProgramID' => ['nullable', 'exists:t_CRMTrainingPrograms,Id'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'TemplateBody' => ['nullable', 'string'],
            'BackgroundImagePath' => ['nullable', 'string', 'max:255', Rule::in(array_keys($this->backgroundImageChoices()))],
            'DefaultIssuingBody' => ['nullable', 'string', 'max:150'],
            'DefaultValidityMonths' => ['nullable', 'integer', 'min:1', 'max:240'],
            'TemplateDocumentFile' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,svg'],
            'IsSample' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $update = [
            'ProgramID' => ! empty($data['ProgramID']) ? (int) $data['ProgramID'] : null,
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'TemplateBody' => $data['TemplateBody'] ?? null,
            'BackgroundImagePath' => $data['BackgroundImagePath'] ?? null,
            'DefaultIssuingBody' => $data['DefaultIssuingBody'] ?? null,
            'DefaultValidityMonths' => $data['DefaultValidityMonths'] ?? null,
            'IsSample' => $request->boolean('IsSample', false),
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ];

        if ($request->hasFile('TemplateDocumentFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::CRM),
                $request->file('TemplateDocumentFile'),
                auth()->user()
            )->document;
            $update['TemplateDocumentId'] = $document->Id;
        }

        $template->update($update);

        return redirect()->route('crm.training.certificate-templates.index')
            ->with('success', 'Certificate template updated.');
    }

    public function destroy($id)
    {
        $template = TrainingCertificateTemplate::findOrFail($id);
        $template->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('crm.training.certificate-templates.index')
            ->with('success', 'Certificate template deactivated.');
    }

    private function placeholderTokens(): array
    {
        return [
            '{{participant_name}}' => 'Participant full name',
            '{{client_id}}' => 'Participant client ID',
            '{{program_name}}' => 'Training program title',
            '{{session_title}}' => 'Session title or code',
            '{{issue_date}}' => 'Certificate issue date',
            '{{expiry_date}}' => 'Certificate expiry date',
            '{{certificate_number}}' => 'Certificate number',
            '{{issuing_body}}' => 'Issuing organization/body',
        ];
    }

    private function backgroundImageChoices(): array
    {
        return [
            'assets/certificates/templates/blue-gold-arc.svg' => 'Blue Gold Arc',
            'assets/certificates/templates/emerald-modern.svg' => 'Emerald Modern',
            'assets/certificates/templates/slate-ribbon.svg' => 'Slate Ribbon',
        ];
    }
}
