<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Section;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        $sections = Section::withCount('criteria')->get();
        return view('procurement.tendering.settings.sections', compact('sections'));
    }

    public function create(): View
    {
        return view('procurement.tendering.settings.section-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $section = Section::create([
                'SectionName' => $validated['name'],
                'Description' => $validated['desc'] ?? null,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'IsActive' => 1
            ]);

            activity()
                ->performedOn($section)
                ->causedBy(Auth::id())
                ->log('Created section: ' . $validated['name']);

            DB::commit();
            return redirect()->route('prequalification.sections.index')->with('success', 'Section created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Failed to create section: ' . $th->getMessage());
        }
    }

    public function show(Section $section): View
    {
        $section->load('criteria');
        return view('procurement.tendering.settings.section_show', compact('section'));
    }

    public function edit(Section $section): View
    {
        return view('procurement.tendering.settings.section-edit', compact('section'));
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        $oldValues = $section->getOriginal();

        $section->update([
            'SectionName' => $validated['name'],
            'Description' => $validated['desc'] ?? null,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now()
        ]);

        activity()
            ->performedOn($section)
            ->causedBy(auth()->user())
            ->withProperties(['old' => $oldValues, 'new' => $section->getChanges()])
            ->log('Updated section: ' . $section->SectionName);

        return redirect()->route('prequalification.sections.index')->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        $sectionName = $section->SectionName;
        $section->delete();

        activity()
            ->performedOn($section)
            ->causedBy(auth()->user())
            ->withProperties(['section_name' => $sectionName])
            ->log('Deleted section: ' . $sectionName);

        return redirect()->route('prequalification.sections.index')->with('success', 'Section deleted successfully.');
    }
}
