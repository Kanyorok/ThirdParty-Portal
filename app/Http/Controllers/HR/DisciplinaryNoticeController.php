<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\EmailStatusEnum;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Discipline\DisciplinaryCaseStatusLog;
use App\Models\HR\Discipline\DisciplinaryLetterTemplate;
use App\Models\HR\Discipline\DisciplinaryNotice;
use App\Models\DMS\Document;
use App\Models\Legal\LegalTemplate;
use App\Services\CRMEmailService;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DisciplinaryNoticeController extends Controller
{
    public function create($caseId)
    {
        $case = DisciplinaryCase::with('employee')->findOrFail($caseId);
        $notice = DisciplinaryNotice::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $templates = DisciplinaryLetterTemplate::orderBy('LetterType')->get();

        return view('hr.discipline.cases.notice', compact('case', 'notice', 'templates'));
    }

    public function store(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $actor = auth()->user();
        $data = $request->validate([
            'NoticeType' => ['required', 'string', 'max:50'],
            'TemplateID' => [
                'nullable',
                'exists:t_LegalTemplates,Id',
                Rule::requiredIf(fn () => $request->input('DeliveryMethod') === 'Auto'),
            ],
            'IssuedOn' => ['nullable', 'date'],
            'ResponseDueOn' => ['nullable', 'date'],
            'Summary' => ['nullable', 'string'],
            'Status' => ['nullable', 'string', 'max:30'],
            'DeliveryMethod' => ['required', 'in:Auto,Upload'],
            'NoticeDocument' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,rtf,txt'],
            'SendNow' => ['sometimes', 'boolean'],
            'Regenerate' => ['sometimes', 'boolean'],
        ]);

        $notice = DisciplinaryNotice::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $documentId = $notice?->NoticeDocumentId;

        if ($data['DeliveryMethod'] === 'Upload' && !$request->hasFile('NoticeDocument') && !$documentId) {
            return back()->withErrors(['NoticeDocument' => 'Upload a signed notice document.'])->withInput();
        }

        if ($data['DeliveryMethod'] === 'Auto') {
            $template = LegalTemplate::find($data['TemplateID']);
            if (!$documentId || $request->boolean('Regenerate')) {
                try {
                    $documentId = $this->generateNoticeDocument($case, $template, $data, $actor);
                } catch (\Throwable $e) {
                    return back()->withErrors(['NoticeDocument' => 'Failed to generate notice document.'])->withInput();
                }
            }
        }

        if ($data['DeliveryMethod'] === 'Upload' && $request->hasFile('NoticeDocument')) {
            try {
                $documentId = $this->storeNoticeDocument($request->file('NoticeDocument'), $actor);
            } catch (\Throwable $e) {
                return back()->withErrors(['NoticeDocument' => 'Failed to upload notice document. Ensure the file type is supported.'])->withInput();
            }
        }

        $sendNow = $request->boolean('SendNow');
        $deliveryStatus = $notice?->DeliveryStatus ?? 'Draft';
        $sentOn = $notice?->SentOn;
        $sentBy = $notice?->SentBy;
        if ($sendNow) {
            $emailSent = $this->sendNoticeEmail($case, $documentId, $data);
            $deliveryStatus = $emailSent ? 'Sent' : 'Failed';
            $sentOn = now();
            $sentBy = auth()->id();
        }

        if ($notice) {
            $notice->update([
                'NoticeType' => $data['NoticeType'],
                'TemplateID' => $data['TemplateID'] ?? null,
                'NoticeDocumentId' => $documentId,
                'IssuedOn' => $data['IssuedOn'] ?? $notice->IssuedOn ?? now()->toDateString(),
                'ResponseDueOn' => $data['ResponseDueOn'] ?? null,
                'Summary' => $data['Summary'] ?? null,
                'Status' => $data['Status'] ?? $notice->Status ?? 'Issued',
                'DeliveryMethod' => $data['DeliveryMethod'],
                'DeliveryStatus' => $deliveryStatus,
                'SentBy' => $sentBy,
                'SentOn' => $sentOn,
            ]);
        } else {
            DisciplinaryNotice::create([
                'CaseID' => $case->Id,
                'NoticeType' => $data['NoticeType'],
                'TemplateID' => $data['TemplateID'] ?? null,
                'NoticeDocumentId' => $documentId,
                'IssuedOn' => $data['IssuedOn'] ?? now()->toDateString(),
                'ResponseDueOn' => $data['ResponseDueOn'] ?? null,
                'Summary' => $data['Summary'] ?? null,
                'Status' => $data['Status'] ?? 'Issued',
                'DeliveryMethod' => $data['DeliveryMethod'],
                'DeliveryStatus' => $deliveryStatus,
                'SentBy' => $sentBy,
                'SentOn' => $sentOn,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        $this->logStatus($case, 'Under Investigation', $sendNow ? 'Show cause notice sent.' : 'Show cause notice prepared.');

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Notice saved.');
    }

    private function generateNoticeDocument(DisciplinaryCase $case, ?LegalTemplate $template, array $data, $actor): ?int
    {
        $employee = $case->employee;
        $templateBody = $template?->TemplateBody ?? '';
        if ($templateBody === '') {
            $templateBody = "SHOW CAUSE NOTICE\n\nEmployee: {{employee_name}} ({{employee_no}})\nCase: {{case_no}}\nOffence: {{offence}}\nIncident Date: {{incident_date}}\nResponse Due: {{response_due}}\n\nSummary:\n{{summary}}\n";
        }

        $tokens = [
            '{{case_no}}' => $case->CaseNo,
            '{{employee_name}}' => trim(($employee?->FirstName ?? '') . ' ' . ($employee?->LastName ?? '')),
            '{{employee_no}}' => $employee?->EmployeeNo ?? '',
            '{{offence}}' => $case->offence?->Name ?? '',
            '{{incident_date}}' => $case->IncidentDate?->format('Y-m-d') ?? '',
            '{{response_due}}' => $data['ResponseDueOn'] ?? '',
            '{{summary}}' => $data['Summary'] ?? '',
        ];

        $body = str_replace(array_keys($tokens), array_values($tokens), $templateBody);
        $body = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $body);
        $plainText = trim(strip_tags(html_entity_decode($body)));
        $rtf = $this->toRtf($plainText);
        $fileName = 'show_cause_' . Str::slug($case->CaseNo ?: 'case') . '_' . now()->format('Ymd_His') . '.rtf';

        $document = DocumentService::createContent(
            RepositoryService::module(ModulesEnum::HRM),
            ExtensionsEnum::RTF,
            $fileName,
            $rtf,
            $actor
        )->document;

        return $document->Id;
    }

    private function toRtf(string $text): string
    {
        $escaped = str_replace(['\\', '{', '}'], ['\\\\', '\{', '\}'], $text);
        $lines = preg_split('/\r\n|\r|\n/', $escaped) ?: [];
        $rtfLines = array_map(static fn ($line) => $line . '\\par', $lines);

        return "{\\rtf1\\ansi\n" . implode("\n", $rtfLines) . "\n}";
    }

    private function sendNoticeEmail(DisciplinaryCase $case, ?int $documentId, array $data): bool
    {
        $employee = $case->employee;
        if (!$employee?->Email) {
            return false;
        }

        $subject = 'Show Cause Notice - ' . $case->CaseNo;
        $body = '<p>Dear ' . e(trim(($employee->FirstName ?? '') . ' ' . ($employee->LastName ?? ''))) . ',</p>';
        $body .= '<p>You are hereby issued with a show cause notice in relation to case <strong>' . e($case->CaseNo) . '</strong>.</p>';
        if (!empty($data['Summary'])) {
            $body .= '<p><strong>Summary:</strong><br>' . nl2br(e($data['Summary'])) . '</p>';
        }
        if (!empty($data['ResponseDueOn'])) {
            $body .= '<p>Response due by: <strong>' . e($data['ResponseDueOn']) . '</strong>.</p>';
        }
        if ($documentId) {
            $document = Document::find($documentId);
            if ($document) {
                $body .= '<p>Notice document: <a href="' . route('file.preview', ['document' => $document->DocumentId]) . '">View document</a></p>';
            }
        }
        $body .= '<p>Regards,<br>HR Department</p>';

        try {
            $service = CRMEmailService::createRaw(
                auth()->user(),
                $subject,
                $body,
                [[$employee->Email => $employee->FirstName . ' ' . $employee->LastName]],
                'HREmployees',
                (string)$employee->Id
            );
            $service->send(true);
            return $service->crmEmail->Status === EmailStatusEnum::Sent;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function storeNoticeDocument($file, $actor): int
    {
        $repository = RepositoryService::module(ModulesEnum::HRM);

        try {
            return DocumentService::createUpload($repository, $file, $actor)->document->Id;
        } catch (\Throwable $e) {
            $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            $enum = ExtensionsEnum::tryFrom($extension);
            if (!$enum) {
                throw $e;
            }
            $document = DocumentService::createContent(
                $repository,
                $enum,
                $file->getClientOriginalName(),
                $file->getContent(),
                $actor
            )->document;
            return $document->Id;
        }
    }

    private function logStatus(DisciplinaryCase $case, string $toStatus, ?string $remarks = null): void
    {
        DisciplinaryCaseStatusLog::create([
            'CaseID' => $case->Id,
            'FromStatus' => $case->Status,
            'ToStatus' => $toStatus,
            'Remarks' => $remarks,
            'ChangedBy' => auth()->id(),
            'ChangedOn' => now(),
        ]);

        if ($case->Status !== $toStatus) {
            $case->update([
                'Status' => $toStatus,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }
    }
}
