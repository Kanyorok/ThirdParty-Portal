<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQSettingSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RFQSettingSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = RFQSettingSection::all();
        return view('procurement.rfqs.settings.sections', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            RFQSettingSection::create([
                'SectionName' => $request->input('name'),
                'Description' => $request->input('desc', null),
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->performedOn(new RFQSettingSection())
                ->causedBy(Auth::id())
                ->log('Created a new RFQ section: ' . $request->input('name'));

            DB::commit();
            return back()->with('success', 'Section created successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            activity()
                ->performedOn(new RFQSettingSection())
                ->causedBy(Auth::id())
                ->log('Failed to create RFQ section: ' . $th->getMessage());

            return back()->with('error', 'Failed to create section: ' . $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        $section = RFQSettingSection::findOrFail($id);
        $oldValues = $section->getOriginal();

        $section->SectionName = $request->name;
        $section->Description = $request->desc;
        $section->ModifiedBy = auth()->id();
        $section->ModifiedOn = now();
        $section->save();

        activity()
            ->performedOn($section)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldValues,
                'new' => $section->getChanges()
            ])
            ->log('Updated RFQ section: ' . $section->SectionName);

        return redirect()->back()->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $section = RFQSettingSection::findOrFail($id);
        $sectionName = $section->SectionName;

        $section->DeletedBy = auth()->id();
        $section->save();

        activity()
            ->performedOn($section)
            ->causedBy(auth()->user())
            ->withProperties(['section_name' => $sectionName])
            ->log('Deleted RFQ section: ' . $sectionName);

        $section->delete();

        return redirect()->back()->with('success', 'Section deleted successfully.');
    }
}

