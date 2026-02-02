<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceTrainingSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplianceTrainingController extends Controller
{
    public function index()
    {
        $trainings = ComplianceTrainingSession::all();

        return view('legal.compliance.trainings.index', compact('trainings'));
    }

    public function create()
    {
        $types = DB::table('t_TrainingTypes')->pluck('Name', 'Id');

        return view('legal.compliance.trainings.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Topic' => 'required|string|max:255',
            'TrainingTypeID' => 'nullable|exists:t_TrainingTypes,Id',
            'Facilitator' => 'nullable|string|max:150',
            'SessionDate' => 'required|date',
            'Duration' => 'nullable|string|max:50',
            'Materials' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx',
        ]);

        $fileName = null;
        $mime = null;
        $path = null;
        if ($request->hasFile('Materials')) {
            $file = $request->file('Materials');
            $fileName = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $path = $file->store('compliance/trainings');
        }

        ComplianceTrainingSession::create([
            'Topic' => $request->Topic,
            'TrainingTypeID' => $request->TrainingTypeID,
            'Facilitator' => $request->Facilitator,
            'SessionDate' => $request->SessionDate,
            'Duration' => $request->Duration,
            'MaterialsFileName' => $fileName,
            'MaterialsMimeType' => $mime,
            'MaterialsFilePath' => $path,
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.compliance.trainings.index')->with('success', 'Training created.');
    }

    public function show($id)
    {
        $training = \App\Models\Legal\ComplianceTrainingSession::with(['participants','certifications'])->findOrFail($id);
        $users = \DB::table('t_Users')->pluck('Name', 'Id');

        return view('legal.compliance.trainings.show', compact('training', 'users'));
    }

    public function addParticipant(Request $request, $id)
    {
        $request->validate([
            'UserID' => 'required|exists:t_Users,Id',
        ]);

        // ✅ Check if participant already exists
        $exists = \App\Models\Legal\ComplianceTrainingParticipant::where('TrainingID', $id)
                    ->where('UserID', $request->UserID)
                    ->exists();

        if ($exists) {
            return back()->with('error', 'This participant is already registered for the training.');
        }

        \App\Models\Legal\ComplianceTrainingParticipant::create([
            'TrainingID' => $id,
            'UserID' => $request->UserID,
            'Attended' => 0,
            'RegisteredOn' => now(),
        ]);

        return back()->with('success', 'Participant registered.');
    }

    public function markAttendance($trainingId, $participantId)
    {
        $participant = \App\Models\Legal\ComplianceTrainingParticipant::findOrFail($participantId);
        $participant->Attended = 1;
        $participant->save();

        return back()->with('success', 'Attendance marked.');
    }

    public function addCertification(Request $request, $trainingId)
    {
        $request->validate([
            'UserID' => 'required|exists:t_Users,Id',
            'CertificationName' => 'required|string|max:200',
            'IssueDate' => 'nullable|date',
            'ExpiryDate' => 'nullable|date',
        ]);

        \App\Models\Legal\ComplianceCertification::create([
            'UserID' => $request->UserID,
            'TrainingID' => $trainingId,
            'CertificationName' => $request->CertificationName,
            'IssueDate' => $request->IssueDate ?? now(),
            'ExpiryDate' => $request->ExpiryDate,
            'Status' => 'Active',
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return back()->with('success', 'Certification issued successfully.');
    }

    public function quickIssueCertification($trainingId, $participantId)
    {
        $participant = \App\Models\Legal\ComplianceTrainingParticipant::findOrFail($participantId);

        \App\Models\Legal\ComplianceCertification::create([
            'UserID' => $participant->UserID,
            'TrainingID' => $trainingId,
            'CertificationName' => 'Completion of Training: ' . $participant->training->Topic,
            'IssueDate' => now(),
            'ExpiryDate' => null,
            'Status' => 'Active',
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return back()->with('success', 'Certification issued to ' . $participant->training->Topic . ' participant.');
    }

    public function bulkIssueCertifications(Request $request, $trainingId)
    {
        $request->validate([
            'participants' => 'required|array|min:1',
            'participants.*' => 'integer|exists:t_ComplianceTrainingParticipants,Id',
        ]);

        $training = \App\Models\Legal\ComplianceTrainingSession::findOrFail($trainingId);

        foreach ($request->participants as $participantId) {
            $participant = \App\Models\Legal\ComplianceTrainingParticipant::findOrFail($participantId);

            // Prevent duplicates
            $exists = \App\Models\Legal\ComplianceCertification::where('TrainingID', $trainingId)
                        ->where('UserID', $participant->UserID)
                        ->exists();

            if (! $exists) {
                \App\Models\Legal\ComplianceCertification::create([
                    'UserID' => $participant->UserID,
                    'TrainingID' => $trainingId,
                    'CertificationName' => 'Completion of Training: ' . $training->Topic,
                    'IssueDate' => now(),
                    'ExpiryDate' => null,
                    'Status' => 'Active',
                    'CreatedBy' => auth()->id() ?? 1,
                    'CreatedOn' => now(),
                ]);
            }
        }

        return back()->with('success', 'Certifications issued to selected participants.');
    }

    private function generateCertificate($participant, $training, $certification)
    {
        $pdf = Pdf::loadView('legal.compliance.certificates.template', [
            'participant' => $participant,
            'training' => $training,
            'certification' => $certification,
        ]);

        $fileName = 'certificate_' . $participant->UserID . '_' . $training->Id . '.pdf';
        $filePath = 'compliance/certificates/' . $fileName;

        Storage::put($filePath, $pdf->output());

        return [
            'fileName' => $fileName,
            'filePath' => $filePath,
            'mime' => 'application/pdf',
        ];
    }
}
