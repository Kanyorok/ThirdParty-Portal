<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Exit\ExitInterview;
use App\Models\HR\Exit\ExitInterviewQuestion;
use App\Models\HR\Exit\ExitInterviewResponse;
use App\Models\HR\Exit\ExitRequest;
use Illuminate\Http\Request;

class ExitInterviewController extends Controller
{
    public function index()
    {
        $interviews = ExitInterview::with(['exit.employee', 'interviewer'])
            ->orderByDesc('InterviewDate')
            ->paginate(30);

        return view('hr.exit.interviews.index', compact('interviews'));
    }

    public function edit($exitId)
    {
        $exit = ExitRequest::with(['employee', 'interviews'])->findOrFail($exitId);
        $interview = $exit->interviews->first();
        $employees = Employee::where('Id', '!=', $exit->EmployeeID)
            ->orderBy('FirstName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);
        $questions = ExitInterviewQuestion::where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->orderBy('Sequence')
            ->get(['Id', 'Question', 'Sequence']);
        $responses = ExitInterviewResponse::where('ExitID', $exit->Id)
            ->get()
            ->keyBy('QuestionID');

        return view('hr.exit.interviews.edit', compact('exit', 'interview', 'employees', 'questions', 'responses'));
    }

    public function show($exitId)
    {
        $exit = ExitRequest::with(['employee', 'interviews.interviewer'])->findOrFail($exitId);
        $interview = $exit->interviews->first();
        $questions = ExitInterviewQuestion::where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->orderBy('Sequence')
            ->get(['Id', 'Question', 'Sequence']);
        $responses = ExitInterviewResponse::where('ExitID', $exit->Id)
            ->get()
            ->keyBy('QuestionID');

        return view('hr.exit.interviews.show', compact('exit', 'interview', 'questions', 'responses'));
    }

    public function store(Request $request, $exitId)
    {
        $exit = ExitRequest::findOrFail($exitId);
        $data = $request->validate([
            'InterviewerID' => ['nullable', 'exists:t_HREmployees,Id'],
            'InterviewDate' => ['nullable', 'date'],
            'Mode' => ['nullable', 'string', 'max:50'],
            'AttritionReason' => ['nullable', 'string', 'max:150'],
            'Notes' => ['nullable', 'string'],
            'Answers' => ['nullable', 'array'],
            'Answers.*' => ['nullable', 'string'],
        ]);

        if (! empty($data['InterviewerID']) && (int) $data['InterviewerID'] === (int) $exit->EmployeeID) {
            return back()->withErrors(['InterviewerID' => 'The exiting employee cannot be selected as the interviewer.'])->withInput();
        }

        ExitInterview::updateOrCreate(
            ['ExitID' => $exit->Id],
            [
                'InterviewerID' => $data['InterviewerID'] ?? null,
                'InterviewDate' => $data['InterviewDate'] ?? null,
                'Mode' => $data['Mode'] ?? null,
                'AttritionReason' => $data['AttritionReason'] ?? null,
                'Notes' => $data['Notes'] ?? null,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]
        );

        $questionIds = ExitInterviewQuestion::where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->pluck('Id')
            ->all();
        $answers = $data['Answers'] ?? [];
        foreach ($answers as $questionId => $answer) {
            $questionId = (int) $questionId;
            if (! in_array($questionId, $questionIds, true)) {
                continue;
            }
            ExitInterviewResponse::updateOrCreate(
                ['ExitID' => $exit->Id, 'QuestionID' => $questionId],
                [
                    'Answer' => is_string($answer) ? trim($answer) : null,
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                ]
            );
        }

        return redirect()->route('hr.exit.interviews.edit', $exit->Id)->with('success', 'Exit interview saved.');
    }
}
