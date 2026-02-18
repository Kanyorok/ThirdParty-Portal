<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobInterviewQuestionGroup;
use Illuminate\Http\Request;

class JobInterviewQuestionGroupController extends Controller
{
    public function index()
    {
        $groups = JobInterviewQuestionGroup::orderBy('Name')->get();

        return view('hr.recruitment.interviews.question-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('hr.recruitment.interviews.question-groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        JobInterviewQuestionGroup::create([
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.interview-question-groups.index')
            ->with('success', 'Question group saved.');
    }

    public function edit($id)
    {
        $group = JobInterviewQuestionGroup::findOrFail($id);

        return view('hr.recruitment.interviews.question-groups.edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $group = JobInterviewQuestionGroup::findOrFail($id);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $group->update([
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
        ]);

        return redirect()->route('hr.recruitment.interview-question-groups.index')
            ->with('success', 'Question group updated.');
    }
}
