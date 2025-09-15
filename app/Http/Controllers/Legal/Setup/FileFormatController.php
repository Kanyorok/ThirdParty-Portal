<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\FileFormat;
use Illuminate\Http\Request;

class FileFormatController extends Controller
{
    public function index()
    {
        $formats = FileFormat::orderBy('Name')->get();
        return view('legal.setup.file_formats.index', compact('formats'));
    }

    public function create()
    {
        return view('legal.setup.file_formats.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:50',
            'MimeType' => 'nullable|string|max:100',
        ]);

        FileFormat::create($validated + ['IsActive' => 1]);
        return redirect()->route('legal.setup.file_formats.index')
            ->with('success', 'File Format added successfully.');
    }

    public function edit($id)
    {
        $format = FileFormat::findOrFail($id);
        return view('legal.setup.file_formats.edit', compact('format'));
    }

    public function update(Request $request, $id)
    {
        $format = FileFormat::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:50',
            'MimeType' => 'nullable|string|max:100',
            'IsActive' => 'nullable|boolean',
        ]);

        $format->update($validated);
        return redirect()->route('legal.setup.file_formats.index')
            ->with('success', 'File Format updated successfully.');
    }

    public function destroy($id)
    {
        $format = FileFormat::findOrFail($id);
        $format->delete();
        return back()->with('success', 'File Format deleted.');
    }
}
