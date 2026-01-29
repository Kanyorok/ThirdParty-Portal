<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\TrainingType;
use Illuminate\Http\Request;

class TrainingTypeController extends Controller
{
    public function index()
    {
        $trainings = TrainingType::orderBy('Name')->get();

        return view('legal.setup.training_types.index', compact('trainings'));
    }

    public function create()
    {
        return view('legal.setup.training_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
        ]);

        TrainingType::create($validated + ['IsActive' => 1]);

        return redirect()->route('legal.setup.training_types.index')
            ->with('success', 'Training Type added successfully.');
    }

    public function edit($id)
    {
        $training = TrainingType::findOrFail($id);

        return view('legal.setup.training_types.edit', compact('training'));
    }

    public function update(Request $request, $id)
    {
        $training = TrainingType::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        $training->update($validated);

        return redirect()->route('legal.setup.training_types.index')
            ->with('success', 'Training Type updated successfully.');
    }

    public function destroy($id)
    {
        $training = TrainingType::findOrFail($id);
        $training->delete();

        return back()->with('success', 'Training Type deleted.');
    }
}
