<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQSection;
use App\Models\Procurement\RFQSettingSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RFQSectionController extends Controller
{
    /**
     * Display a listing of the RFQ sections.
     */
    public function evaluationSetup()
    {
        $rfqs = RFQ::withCount(['sections', 'criteria'])
            ->with('sections')
            ->get();
        $sections = RFQSettingSection::all();
        $rfqList = RFQ::select('Id', 'RFQNumber')->get(); // or any other fields you need

        return view('procurement.rfqcriteriasetup.rfqevaluations', compact('rfqs', 'sections', 'rfqList'));
    }


    /**
     * Store selected sections and weights for a given RFQ.
     */
    public function saveEvaluation(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required|exists:t_RFQ,Id',
            'sections' => 'required|array',
            'weights' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->sections as $sectionId) {
                // Use the sectionId as the key in weights[]
                $weight = $request->weights[$sectionId] ?? 0;

                RFQSection::create([
                    'RFQID' => $request->rfq_id,
                    'SectionID' => $sectionId,
                    'Weight' => $weight,
                    'IsActive' => true,
                    'Comments' => null,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            }

            activity()
                ->performedOn(new RFQSection())
                ->causedBy(Auth::id())
                ->log('Assigned sections to RFQ ID: ' . $request->rfq_id);

            DB::commit();
            return back()->with('success', 'RFQ Evaluation sections saved successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Error saving RFQ Evaluation sections: ' . $th->getMessage());
        }
    }

    public function index()
    {
        $sections = RFQSection::all();
        return view('procurement.rfq.settings.sections', compact('sections'));
    }

    /**
     * Store a newly created RFQ section.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            RFQSection::create([
                'SectionName' => $request->input('name'),
                'Description' => $request->input('desc', null),
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->performedOn(new RFQSection())
                ->causedBy(Auth::id())
                ->log('Created a new RFQ section: ' . $request->input('name'));

            DB::commit();
            return back()->with('success', 'RFQ section created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new RFQSection())
                ->causedBy(Auth::id())
                ->log('Failed to create RFQ section: ' . $th->getMessage());

            return back()->with('error', 'Failed to create RFQ section: ' . $th->getMessage());
        }
    }

    /**
     * Update the specified RFQ section.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        $section = RFQSection::findOrFail($id);
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

        return redirect()->back()->with('success', 'RFQ section updated successfully.');
    }

    /**
     * Remove the specified RFQ section.
     */
    public function destroy(string $id)
    {
        $section = RFQSection::findOrFail($id);
        $sectionName = $section->SectionName;

        $section->DeletedBy = auth()->id();
        $section->save();

        activity()
            ->performedOn($section)
            ->causedBy(auth()->user())
            ->withProperties(['section_name' => $sectionName])
            ->log('Deleted RFQ section: ' . $sectionName);

        $section->delete();

        return redirect()->back()->with('success', 'RFQ section deleted successfully.');
    }
}
