<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Applicant;
use App\Models\HR\JobApplication;
use App\Models\HR\JobApplicationDocument;
use App\Models\HR\JobApplicationScreening;
use App\Models\HR\JobOpening;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    private const STATUSES = ['Applied', 'Under Screening', 'Shortlist Pending', 'Shortlisted', 'Interview', 'Offer', 'Hired', 'Rejected', 'Withdrawn'];

    public function index(Request $request)
    {
        $query = JobApplication::with(['opening', 'applicant'])
            ->orderByDesc('Id');

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Applied') {
                $query->whereIn('Status', ['Applied', 'New']);
            } elseif ($status === 'Under Screening') {
                $query->whereIn('Status', ['Under Screening', 'Screening']);
            } elseif ($status === 'Shortlist Pending') {
                $query->where('Status', 'Shortlist Pending');
            } else {
                $query->where('Status', $status);
            }
        }
        if ($request->filled('opening_id')) {
            $query->where('JobOpeningID', $request->opening_id);
        }

        $applications = $query->paginate(20);
        $openings = JobOpening::where('Status', 'Open')->orderBy('Title')->get(['Id', 'Title']);
        $statusList = self::STATUSES;

        return view('hr.recruitment.applications.index', compact('applications', 'openings', 'statusList'));
    }

    public function create(Request $request)
    {
        $openings = JobOpening::where('Status', 'Open')->orderBy('Title')->get();
        $opening = null;
        if ($request->filled('opening_id')) {
            $opening = $openings->firstWhere('Id', (int)$request->opening_id);
        }

        return view('hr.recruitment.applications.create', compact('openings', 'opening'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'JobOpeningID' => ['required', 'integer', 'exists:t_HRJobOpenings,Id'],
            'FirstName' => ['required', 'string', 'max:100'],
            'LastName' => ['required', 'string', 'max:100'],
            'OtherNames' => ['nullable', 'string', 'max:100'],
            'Email' => ['nullable', 'email', 'max:150'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Gender' => ['nullable', 'string', 'max:20'],
            'DateOfBirth' => ['nullable', 'date'],
            'Address' => ['nullable', 'string', 'max:255'],
            'Source' => ['nullable', 'string', 'max:100'],
            'ExpectedSalary' => ['nullable', 'numeric', 'min:0'],
            'NoticePeriodDays' => ['nullable', 'integer', 'min:0'],
            'Notes' => ['nullable', 'string', 'max:2000'],
            'Resume' => ['nullable', 'file', 'max:5120'],
            'CoverLetter' => ['nullable', 'file', 'max:5120'],
            'documents.*' => ['nullable', 'file', 'max:5120'],
            'documents_category.*' => ['nullable', 'string', 'max:100'],
            'documents_description.*' => ['nullable', 'string', 'max:255'],
        ]);

        $applicant = Applicant::query()
            ->when($data['Email'] ?? null, fn ($q) => $q->where('Email', $data['Email']))
            ->when(! $data['Email'] && ($data['Phone'] ?? null), fn ($q) => $q->where('Phone', $data['Phone']))
            ->first();

        if (! $applicant) {
            $applicant = Applicant::create([
                'FirstName' => $data['FirstName'],
                'LastName' => $data['LastName'],
                'OtherNames' => $data['OtherNames'] ?? null,
                'Email' => $data['Email'] ?? null,
                'Phone' => $data['Phone'] ?? null,
                'Gender' => $data['Gender'] ?? null,
                'DateOfBirth' => $data['DateOfBirth'] ?? null,
                'Address' => $data['Address'] ?? null,
                'Source' => $data['Source'] ?? null,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        $resumePath = null;
        if ($request->hasFile('Resume')) {
            $resumePath = $request->file('Resume')->store('job-applications', 'public');
        }
        $coverPath = null;
        if ($request->hasFile('CoverLetter')) {
            $coverPath = $request->file('CoverLetter')->store('job-applications', 'public');
        }

        $application = JobApplication::create([
            'JobOpeningID' => $data['JobOpeningID'],
            'ApplicantID' => $applicant->Id,
            'Status' => 'Applied',
            'ExpectedSalary' => $data['ExpectedSalary'] ?? null,
            'NoticePeriodDays' => $data['NoticePeriodDays'] ?? null,
            'ResumePath' => $resumePath,
            'CoverLetterPath' => $coverPath,
            'Source' => $data['Source'] ?? null,
            'Notes' => $data['Notes'] ?? null,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        foreach ($request->file('documents', []) as $idx => $file) {
            if (! $file) {
                continue;
            }
            $storedPath = $file->store('job-applications', 'public');
            JobApplicationDocument::create([
                'ApplicationID' => $application->Id,
                'FileName' => $file->getClientOriginalName(),
                'FilePath' => $storedPath,
                'Category' => $request->input("documents_category.$idx") ?: null,
                'Description' => $request->input("documents_description.$idx") ?: null,
                'UploadedBy' => auth()->id(),
                'UploadedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.applications.index')
            ->with('success', 'Application captured.');
    }

    public function show($id)
    {
        $application = JobApplication::with(['opening', 'applicant', 'documents', 'screenings', 'interviewCandidates.session', 'offers'])
            ->findOrFail($id);

        return view('hr.recruitment.applications.show', compact('application'));
    }

    public function screen(Request $request, $id)
    {
        $data = $request->validate([
            'Status' => ['required', 'string', 'max:30'],
            'Score' => ['nullable', 'numeric'],
            'Notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application = JobApplication::findOrFail($id);
        $status = $data['Status'];
        if (! in_array($status, ['Shortlisted', 'Rejected', 'Under Screening'], true)) {
            $status = 'Under Screening';
        }
        $appStatus = match ($status) {
            'Shortlisted' => 'Shortlist Pending',
            'Rejected' => 'Rejected',
            default => 'Under Screening',
        };
        $application->update([
            'Status' => $appStatus,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        JobApplicationScreening::create([
            'ApplicationID' => $application->Id,
            'Status' => $status,
            'ApprovalStatus' => $status === 'Shortlisted' ? 'Pending' : null,
            'Score' => $data['Score'] ?? null,
            'Notes' => $data['Notes'] ?? null,
            'ScreenedBy' => auth()->id(),
            'ScreenedOn' => now(),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.applications.show', $application->Id)
            ->with('success', 'Screening updated.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'Notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $application = JobApplication::findOrFail($id);
        $application->update([
            'Status' => 'Rejected',
            'Notes' => $data['Notes'] ?? $application->Notes,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.applications.show', $application->Id)
            ->with('success', 'Application rejected.');
    }

    public function shortlist($id)
    {
        return redirect()->route('hr.recruitment.applications.show', $id)->with('success', 'Complete screening to shortlist or reject.');
    }

    public function approveShortlist($id)
    {
        $application = JobApplication::with('screenings')->findOrFail($id);
        $latest = $application->screenings
            ->sortByDesc(fn ($item) => $item->ScreenedOn ?? $item->CreatedOn)
            ->first();

        if (! $latest || $latest->Status !== 'Shortlisted') {
            return redirect()->route('hr.recruitment.applications.show', $application->Id)
                ->withErrors(['status' => 'Only shortlisted screenings can be approved.']);
        }
        if ($latest->ApprovalStatus === 'Approved') {
            return redirect()->route('hr.recruitment.applications.show', $application->Id)
                ->withErrors(['status' => 'Shortlist already approved.']);
        }

        $latest->update([
            'ApprovalStatus' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
        ]);

        $application->update([
            'Status' => 'Shortlisted',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.applications.show', $application->Id)
            ->with('success', 'Shortlist approved.');
    }
}
