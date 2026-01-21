<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitInterviewQuestion;
use Illuminate\Http\Request;

class ExitInterviewQuestionController extends Controller
{
    public function index()
    {
        $questions = ExitInterviewQuestion::whereNull('DeletedOn')
            ->orderBy('Sequence')
            ->orderBy('Id')
            ->get();

        return view('hr.exit.config.interview_questions.index', compact('questions'));
    }

    public function create()
    {
        return view('hr.exit.config.interview_questions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Question' => ['required', 'string', 'max:255'],
            'Sequence' => ['nullable', 'integer', 'min:1'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        ExitInterviewQuestion::create([
            'Question' => $data['Question'],
            'Sequence' => $data['Sequence'] ?? 1,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-interview-questions.index')->with('success', 'Exit interview question saved.');
    }

    public function edit($id)
    {
        $question = ExitInterviewQuestion::whereNull('DeletedOn')->findOrFail($id);
        return view('hr.exit.config.interview_questions.edit', compact('question'));
    }

    public function update(Request $request, $id)
    {
        $question = ExitInterviewQuestion::whereNull('DeletedOn')->findOrFail($id);
        $data = $request->validate([
            'Question' => ['required', 'string', 'max:255'],
            'Sequence' => ['nullable', 'integer', 'min:1'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $question->update([
            'Question' => $data['Question'],
            'Sequence' => $data['Sequence'] ?? 1,
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-interview-questions.index')->with('success', 'Exit interview question updated.');
    }
}
