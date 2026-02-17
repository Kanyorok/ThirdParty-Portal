<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobApplication;
use App\Models\HR\JobOffer;
use App\Models\HR\OnboardingQueue;
use App\Models\HR\OnboardingTask;
use Illuminate\Http\Request;

class JobOfferController extends Controller
{
    private const STATUSES = ['Draft', 'Submitted', 'Approved', 'Sent', 'Accepted', 'Rejected'];

    public function index()
    {
        $offers = JobOffer::with(['application.applicant', 'application.opening'])
            ->orderByDesc('Id')
            ->paginate(20);

        return view('hr.recruitment.offers.index', compact('offers'));
    }

    public function create(Request $request)
    {
        $openings = \App\Models\HR\JobOpening::orderBy('Title')->get(['Id', 'Title']);
        $selectedOpening = null;
        if ($request->filled('opening_id')) {
            $selectedOpening = $openings->firstWhere('Id', (int)$request->opening_id);
        }

        $applicationsQuery = JobApplication::with(['applicant', 'opening'])
            ->where('Status', 'Offer');
        if ($selectedOpening) {
            $applicationsQuery->where('JobOpeningID', $selectedOpening->Id);
        }
        $applications = $applicationsQuery->orderByDesc('Id')->get();
        $selectedApplication = null;
        if ($request->filled('application_id')) {
            $selectedApplication = $applications->firstWhere('Id', (int)$request->application_id);
        }

        return view('hr.recruitment.offers.create', compact(
            'applications',
            'selectedApplication',
            'openings',
            'selectedOpening'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ApplicationID' => ['required', 'integer', 'exists:t_HRJobApplications,Id'],
            'OfferDate' => ['nullable', 'date'],
            'SalaryOffered' => ['nullable', 'numeric', 'min:0'],
            'Benefits' => ['nullable', 'string'],
            'Notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $request->input('Action', 'draft');
        $status = $action === 'submit' ? 'Submitted' : 'Draft';

        $offer = JobOffer::create([
            'ApplicationID' => $data['ApplicationID'],
            'OfferDate' => $data['OfferDate'] ?? null,
            'SalaryOffered' => $data['SalaryOffered'] ?? null,
            'Benefits' => $data['Benefits'] ?? null,
            'Notes' => $data['Notes'] ?? null,
            'Status' => $status,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        $application = JobApplication::find($data['ApplicationID']);
        if ($application && !in_array($application->Status, ['Rejected', 'Withdrawn', 'Hired'], true)) {
            $application->update([
                'Status' => 'Offer',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.offers.show', $offer->Id)->with('success', 'Offer saved.');
    }

    public function show($id)
    {
        $offer = JobOffer::with(['application.applicant', 'application.opening', 'onboarding'])
            ->findOrFail($id);

        return view('hr.recruitment.offers.show', compact('offer'));
    }

    public function edit($id)
    {
        $offer = JobOffer::with(['application.applicant', 'application.opening'])->findOrFail($id);
        $applications = JobApplication::with(['applicant', 'opening'])
            ->whereNotIn('Status', ['Rejected', 'Withdrawn', 'Hired'])
            ->orderByDesc('Id')
            ->get();

        return view('hr.recruitment.offers.edit', compact('offer', 'applications'));
    }

    public function update(Request $request, $id)
    {
        $offer = JobOffer::findOrFail($id);
        $data = $request->validate([
            'ApplicationID' => ['required', 'integer', 'exists:t_HRJobApplications,Id'],
            'OfferDate' => ['nullable', 'date'],
            'SalaryOffered' => ['nullable', 'numeric', 'min:0'],
            'Benefits' => ['nullable', 'string'],
            'Notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $request->input('Action');
        if ($action === 'submit' && $offer->Status === 'Draft') {
            $offer->Status = 'Submitted';
        }

        $offer->update([
            'ApplicationID' => $data['ApplicationID'],
            'OfferDate' => $data['OfferDate'] ?? null,
            'SalaryOffered' => $data['SalaryOffered'] ?? null,
            'Benefits' => $data['Benefits'] ?? null,
            'Notes' => $data['Notes'] ?? null,
            'Status' => $offer->Status,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.offers.show', $offer->Id)->with('success', 'Offer updated.');
    }

    public function approve($id)
    {
        $offer = JobOffer::findOrFail($id);
        if (!in_array($offer->Status, ['Submitted', 'Draft'], true)) {
            return redirect()->route('hr.recruitment.offers.show', $offer->Id)
                ->withErrors(['status' => 'Only draft/submitted offers can be approved.']);
        }

        $offer->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.offers.show', $offer->Id)->with('success', 'Offer approved.');
    }

    public function send($id)
    {
        $offer = JobOffer::findOrFail($id);
        if ($offer->Status !== 'Approved') {
            return redirect()->route('hr.recruitment.offers.show', $offer->Id)
                ->withErrors(['status' => 'Only approved offers can be sent.']);
        }

        $offer->update([
            'Status' => 'Sent',
            'SentOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.offers.show', $offer->Id)->with('success', 'Offer sent.');
    }

    public function accept($id)
    {
        $offer = JobOffer::findOrFail($id);
        if (!in_array($offer->Status, ['Approved', 'Sent'], true)) {
            return redirect()->route('hr.recruitment.offers.show', $offer->Id)
                ->withErrors(['status' => 'Offer must be approved/sent before acceptance.']);
        }

        $offer->update([
            'Status' => 'Accepted',
            'AcceptedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $application = $offer->application;
        if ($application) {
            $application->update([
                'Status' => 'Hired',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }

        $this->ensureOnboardingQueue($offer);
        $queue = OnboardingQueue::where('OfferID', $offer->Id)->first();
        if ($queue) {
            return redirect()->route('hr.recruitment.onboarding.show', $queue->Id)
                ->with('success', 'Offer accepted. Onboarding created.');
        }

        return redirect()->route('hr.recruitment.offers.show', $offer->Id)
            ->with('success', 'Offer accepted.');
    }

    public function reject($id)
    {
        $offer = JobOffer::findOrFail($id);
        $offer->update([
            'Status' => 'Rejected',
            'RejectedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $application = $offer->application;
        if ($application) {
            $application->update([
                'Status' => 'Rejected',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.offers.show', $offer->Id)->with('success', 'Offer rejected.');
    }

    private function ensureOnboardingQueue(JobOffer $offer): void
    {
        $existing = OnboardingQueue::where('OfferID', $offer->Id)->first();
        if ($existing) {
            return;
        }

        $applicant = $offer->application?->applicant;
        $queue = OnboardingQueue::create([
            'OfferID' => $offer->Id,
            'ApplicationID' => $offer->ApplicationID,
            'CandidateName' => $applicant ? $applicant->FirstName.' '.$applicant->LastName : null,
            'Status' => 'Pending',
            'StartDate' => now()->toDateString(),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        foreach ($this->defaultOnboardingTasks() as $task) {
            OnboardingTask::create([
                'OnboardingID' => $queue->Id,
                'Title' => $task,
                'IsRequired' => 1,
                'Status' => 'Pending',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }
    }

    private function defaultOnboardingTasks(): array
    {
        return [
            'Collect identification documents',
            'Sign contract and offer letter',
            'Setup payroll profile',
            'Provision IT/email access',
            'Orientation and policy briefing',
        ];
    }
}
