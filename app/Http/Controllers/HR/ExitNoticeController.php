<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\EmailStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Models\HR\Exit\ExitLetterTemplate;
use App\Models\HR\Exit\ExitNotice;
use App\Models\HR\Exit\ExitRequest;
use App\Models\Legal\LegalTemplate;
use App\Services\CRMEmailService;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExitNoticeController extends Controller
{
    public function create($exitId)
    {
        $exit = ExitRequest::with(['employee', 'exitType', 'case'])->findOrFail($exitId);
        if (strtolower($exit->InitiatorType ?? '') !== 'employer') {
            return redirect()->route('hr.exit.requests.show', $exit->Id)->withErrors([
                'notice' => 'Exit notice is only required for employer-initiated exits.',
            ]);
        }
        if (($exit->exitType?->RequiresCase || $exit->exitType?->IsSummaryDismissal) && !$exit->CaseID) {
            return redirect()->route('hr.exit.requests.show', $exit->Id)->withErrors([
                'notice' => 'This exit requires a disciplinary case reference before issuing a notice.',
            ]);
        }
        $notice = ExitNotice::where('ExitID', $exit->Id)->latest('CreatedOn')->first();
        $mappings = ExitLetterTemplate::orderBy('LetterType')->get();
        $legalTemplates = LegalTemplate::orderBy('Title')->get(['Id', 'Title', 'DocumentType', 'Status']);

        return view('hr.exit.requests.notice', compact('exit', 'notice', 'mappings', 'legalTemplates'));
    }

    public function store(Request $request, $exitId)
    {
        $exit = ExitRequest::with(['employee', 'exitType', 'case'])->findOrFail($exitId);
        if (strtolower($exit->InitiatorType ?? '') !== 'employer') {
            return redirect()->route('hr.exit.requests.show', $exit->Id)->withErrors([
                'notice' => 'Exit notice is only required for employer-initiated exits.',
            ]);
        }
        if (($exit->exitType?->RequiresCase || $exit->exitType?->IsSummaryDismissal) && !$exit->CaseID) {
            return redirect()->route('hr.exit.requests.show', $exit->Id)->withErrors([
                'notice' => 'This exit requires a disciplinary case reference before issuing a notice.',
            ]);
        }
        $actor = auth()->user();

        $data = $request->validate([
            'NoticeType' => ['required', 'string', 'max:50'],
            'TemplateID' => ['nullable', 'integer', 'exists:t_LegalTemplates,Id'],
            'IssuedOn' => ['nullable', 'date'],
            'ResponseDue' => ['nullable', 'date'],
            'Summary' => ['nullable', 'string'],
            'Status' => ['nullable', 'string', 'max:30'],
            'DeliveryMethod' => ['required', 'in:Auto,Upload'],
            'NoticeDocument' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,rtf,txt'],
            'SendNow' => ['sometimes', 'boolean'],
            'Regenerate' => ['sometimes', 'boolean'],
        ]);

        $mapping = ExitLetterTemplate::where('LetterType', $data['NoticeType'])->first();
        $templateId = $data['TemplateID'] ?? $mapping?->TemplateID;

        if ($data['DeliveryMethod'] === 'Auto' && !$templateId) {
            return back()->withErrors(['TemplateID' => 'Select a template or map one for this notice type.'])->withInput();
        }

        $notice = ExitNotice::where('ExitID', $exit->Id)->latest('CreatedOn')->first();
        $documentId = $notice?->DocumentId;

        if ($data['DeliveryMethod'] === 'Upload' && !$request->hasFile('NoticeDocument') && !$documentId) {
            return back()->withErrors(['NoticeDocument' => 'Upload a signed notice document.'])->withInput();
        }

        if ($data['DeliveryMethod'] === 'Auto') {
            $template = $templateId ? LegalTemplate::find($templateId) : null;
            if (!$documentId || $request->boolean('Regenerate')) {
                try {
                    $documentId = $this->generateNoticeDocument($exit, $template, $data, $actor);
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
            $emailSent = $this->sendNoticeEmail($exit, $documentId, $data);
            $deliveryStatus = $emailSent ? 'Sent' : 'Failed';
            $sentOn = now();
            $sentBy = auth()->id();
        }

        if ($notice) {
            $notice->update([
                'NoticeType' => $data['NoticeType'],
                'TemplateID' => $templateId,
                'DeliveryMethod' => $data['DeliveryMethod'],
                'DocumentId' => $documentId,
                'IssuedOn' => $data['IssuedOn'] ?? $notice->IssuedOn ?? now()->toDateString(),
                'ResponseDue' => $data['ResponseDue'] ?? null,
                'Status' => $data['Status'] ?? $notice->Status ?? 'Issued',
                'Summary' => $data['Summary'] ?? null,
                'DeliveryStatus' => $deliveryStatus,
                'SentBy' => $sentBy,
                'SentOn' => $sentOn,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        } else {
            ExitNotice::create([
                'ExitID' => $exit->Id,
                'NoticeType' => $data['NoticeType'],
                'TemplateID' => $templateId,
                'DeliveryMethod' => $data['DeliveryMethod'],
                'DocumentId' => $documentId,
                'IssuedOn' => $data['IssuedOn'] ?? now()->toDateString(),
                'ResponseDue' => $data['ResponseDue'] ?? null,
                'Status' => $data['Status'] ?? 'Issued',
                'Summary' => $data['Summary'] ?? null,
                'DeliveryStatus' => $deliveryStatus,
                'SentBy' => $sentBy,
                'SentOn' => $sentOn,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Notice saved.');
    }

    private function generateNoticeDocument(ExitRequest $exit, ?LegalTemplate $template, array $data, $actor): ?int
    {
        $employee = $exit->employee;
        $templateBody = $template?->TemplateBody ?? '';
        if ($templateBody === '') {
            $templateBody = "EXIT NOTICE\n\nEmployee: {{employee_name}} ({{employee_no}})\nExit No: {{exit_no}}\nExit Type: {{exit_type}}\nEffective Exit Date: {{exit_date}}\n\nSummary:\n{{summary}}\n";
        }

        $tokens = [
            '{{exit_no}}' => $exit->ExitNo,
            '{{employee_name}}' => trim(($employee?->FirstName ?? '') . ' ' . ($employee?->LastName ?? '')),
            '{{employee_no}}' => $employee?->EmployeeNo ?? '',
            '{{exit_type}}' => $exit->exitType?->Name ?? '',
            '{{exit_date}}' => $exit->EffectiveExitDate?->format('Y-m-d') ?? '',
            '{{summary}}' => $data['Summary'] ?? '',
        ];

        $body = str_replace(array_keys($tokens), array_values($tokens), $templateBody);
        $body = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $body);
        $plainText = trim(strip_tags(html_entity_decode($body)));
        $rtf = $this->toRtf($plainText);
        $fileName = 'exit_notice_' . Str::slug($exit->ExitNo ?: 'exit') . '_' . now()->format('Ymd_His') . '.rtf';

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

    private function sendNoticeEmail(ExitRequest $exit, ?int $documentId, array $data): bool
    {
        $employee = $exit->employee;
        if (!$employee?->Email) {
            return false;
        }

        $subject = 'Exit Notice - ' . $exit->ExitNo;
        $body = '<p>Dear ' . e(trim(($employee->FirstName ?? '') . ' ' . ($employee->LastName ?? ''))) . ',</p>';
        $body .= '<p>This is an exit notice regarding <strong>' . e($exit->exitType?->Name ?? 'exit') . '</strong>.</p>';
        if (!empty($data['Summary'])) {
            $body .= '<p><strong>Summary:</strong><br>' . nl2br(e($data['Summary'])) . '</p>';
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
}
