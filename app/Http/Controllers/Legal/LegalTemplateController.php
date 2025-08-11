<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalTemplate;

class LegalTemplateController extends Controller
{
    public function index()
    {
        $templates = LegalTemplate::where('IsActive', 1)->get();
        return view('legal.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('legal.templates.create');
    }

    public function store(Request $request)
    {
            $request->validate([
                'TemplateName' => 'required|string|max:255',
                'DocumentType' => 'required|string|max:100',
                'Version' => 'required|string|max:20',
                'Description' => 'nullable|string',
                'Content' => 'nullable|string',
            ]);

            LegalTemplate::create([
                'TemplateName' => $request->TemplateName,
                'DocumentType' => $request->DocumentType,
                'Version' => $request->Version,
                'Description' => $request->Description,
                'Content' => $request->Content,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'IsActive' => 1,
            ]);

        LegalTemplate::create($request->all());

        return redirect()->route('legal.templates.index')->with('success', 'Template created successfully.');
    }

    public function show($id)
    {
        $template = LegalTemplate::findOrFail($id);
        return view('legal.templates.show', compact('template'));
    }

    public function edit($id)
    {
        $template = LegalTemplate::findOrFail($id);
        return view('legal.templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'TemplateName' => 'required|string|max:255',
            'DocumentType' => 'nullable|string|max:100',
            'Version' => 'nullable|string|max:20',
            'Description' => 'nullable|string|max:1000',
        ]);

        $template = LegalTemplate::findOrFail($id);
        $template->update($request->all());

        return redirect()->route('legal.templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = LegalTemplate::findOrFail($id);
        $template->IsActive = 0;
        $template->save();

        return redirect()->route('legal.templates.index')->with('success', 'Template archived.');
    }
}
