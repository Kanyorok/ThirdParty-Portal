<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobInterviewQuestion;
use App\Models\HR\JobInterviewQuestionGroup;
use Illuminate\Http\Request;

class JobInterviewQuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = JobInterviewQuestion::with('group')->orderBy('Title');
        if ($request->filled('group_id')) {
            $query->where('GroupID', $request->group_id);
        }
        $questions = $query->get();
        $groups = JobInterviewQuestionGroup::orderBy('Name')->get();

        return view('hr.recruitment.interviews.questions.index', compact('questions', 'groups'));
    }

    public function create()
    {
        $groups = JobInterviewQuestionGroup::orderBy('Name')->get();

        return view('hr.recruitment.interviews.questions.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'GroupID' => ['nullable', 'integer', 'exists:t_HRJobInterviewQuestionGroups,Id'],
            'Title' => ['required', 'string', 'max:255'],
            'Guidance' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        JobInterviewQuestion::create([
            'GroupID' => $data['GroupID'] ?? null,
            'Title' => $data['Title'],
            'Guidance' => $data['Guidance'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.interview-questions.index')
            ->with('success', 'Question saved.');
    }

    public function edit($id)
    {
        $question = JobInterviewQuestion::findOrFail($id);
        $groups = JobInterviewQuestionGroup::orderBy('Name')->get();

        return view('hr.recruitment.interviews.questions.edit', compact('question', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $question = JobInterviewQuestion::findOrFail($id);
        $data = $request->validate([
            'GroupID' => ['nullable', 'integer', 'exists:t_HRJobInterviewQuestionGroups,Id'],
            'Title' => ['required', 'string', 'max:255'],
            'Guidance' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $question->update([
            'GroupID' => $data['GroupID'] ?? null,
            'Title' => $data['Title'],
            'Guidance' => $data['Guidance'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
        ]);

        return redirect()->route('hr.recruitment.interview-questions.index')
            ->with('success', 'Question updated.');
    }
}
