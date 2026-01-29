<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegulatoryTaskController extends Controller
{
    public function index()
    {
        return view('legal.compliance.tasks.index', [
            'tasks' => [],
        ]);
    }

    public function create()
    {
        return view('legal.compliance.tasks.create');
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Task created successfully');
    }

    public function edit($id)
    {
        return view('legal.compliance.tasks.edit', [
            'task' => null,
        ]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Task updated successfully');
    }
}
