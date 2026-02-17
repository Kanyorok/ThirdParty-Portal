<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Discipline\DisciplinaryCaseStatusLog;
use App\Models\HR\Discipline\DisciplinaryNotice;
use App\Models\HR\Discipline\DisciplinaryResponse;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;

class DisciplinaryResponseController extends Controller
{
    public function create($caseId)
    {
        $case = DisciplinaryCase::with('employee')->findOrFail($caseId);
        $notices = DisciplinaryNotice::where('CaseID', $case->Id)->orderByDesc('CreatedOn')->get();
        $notice = $notices->first();
        $response = $notice
            ? DisciplinaryResponse::where('NoticeID', $notice->Id)->latest('SubmittedOn')->first()
            : DisciplinaryResponse::where('CaseID', $case->Id)->latest('SubmittedOn')->first();

        return view('hr.discipline.cases.response', compact('case', 'response', 'notices', 'notice'));
    }

    public function store(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $actor = auth()->user();
        $data = $request->validate([
            'NoticeID' => ['required', 'exists:t_HRDisciplinaryNotices,Id'],
            'ResponseText' => ['nullable', 'string'],
            'ResponseDocument' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
        ]);

        $notice = DisciplinaryNotice::where('CaseID', $case->Id)->where('Id', $data['NoticeID'])->first();
        if (!$notice) {
            return back()->withErrors(['NoticeID' => 'Selected notice does not belong to this case.'])->withInput();
        }

        $documentId = null;
        if ($request->hasFile('ResponseDocument')) {
            try {
                $documentId = $this->storeResponseDocument($request->file('ResponseDocument'), $actor);
            } catch (\Throwable $e) {
                return back()->withErrors(['ResponseDocument' => 'Failed to upload response document. Ensure the file type is supported.'])->withInput();
            }
        }

        DisciplinaryResponse::create([
            'CaseID' => $case->Id,
            'NoticeID' => $data['NoticeID'],
            'ResponseText' => $data['ResponseText'] ?? null,
            'SubmittedBy' => auth()->id(),
            'SubmittedOn' => now(),
            'Status' => 'Submitted',
            'ResponseDocumentId' => $documentId,
        ]);

        $notice->update([
            'Status' => 'Responded',
        ]);

        $this->logStatus($case, 'Response Submitted', 'Employee response received.');

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Response recorded.');
    }

    private function storeResponseDocument($file, $actor): int
    {
        $repository = RepositoryService::module(ModulesEnum::HRM);

        try {
            return DocumentService::createUpload($repository, $file, $actor)->document->Id;
        } catch (\Throwable $e) {
            $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            $enum = \App\Enums\Core\ExtensionsEnum::tryFrom($extension);
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
