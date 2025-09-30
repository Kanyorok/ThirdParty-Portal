<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQSection;
use App\Models\Procurement\Section;
use App\Models\Procurement\RFQSettingSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Use Sections maintained at tendering settings (t_Sections)
        $sections = Section::isActive()->get();
    // Include Comments so the modal select can show RFQNumber-Comments
    $rfqList = RFQ::select('Id', 'RFQNumber', 'Comments')->get(); // or any other fields you need

        return view('procurement.rfqcriteriasetup.rfqevaluations', compact('rfqs', 'sections', 'rfqList'));
    }


    /**
     * Store selected sections and weights for a given RFQ.
     */
    public function saveEvaluation(Request $request)
    {
        // log incoming payload for debugging when needed
        Log::debug('[saveEvaluation] payload', $request->all());
        $request->validate([
            'rfq_id' => 'required|exists:t_RFQ,Id',
            'sections' => 'required|array|min:1',
            // weights inputs are disabled for unchecked sections and therefore
            // may not be present in the POST payload. Treat as nullable.
            'weights' => 'nullable|array',
        ]);

        $rfqId = $request->input('rfq_id');
        $sections = $request->input('sections', []) ?: [];
        $weights = $request->input('weights', []) ?: [];

        DB::beginTransaction();
        try {
            // Deactivate any previously assigned sections that are not in the current selection
            $exclude = count($sections) ? $sections : [0];
            RFQSection::where('RFQID', $rfqId)
                ->whereNotIn('SectionID', $exclude)
                ->update([
                    'IsActive' => false,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

            foreach ($sections as $sectionId) {
                $weight = isset($weights[$sectionId]) ? floatval($weights[$sectionId]) : 0.0;

                // Use firstOrNew so we can set CreatedOn when inserting and always set ModifiedOn
                $record = RFQSection::firstOrNew([
                    'RFQID' => $rfqId,
                    'SectionID' => $sectionId,
                ]);

                $isNew = !$record->exists;

                $record->Weight = $weight;
                $record->IsActive = true;
                $record->Comments = null;
                $record->ModifiedBy = Auth::id();
                $record->ModifiedOn = now();

                if ($isNew) {
                    $record->CreatedBy = Auth::id();
                    $record->CreatedOn = now();
                }

                $record->save();

                Log::debug('[saveEvaluation] upserted RFQSection', [
                    'RFQID' => $rfqId,
                    'SectionID' => $sectionId,
                    'RFQSectionID' => $record->{$record->getKeyName()},
                    'is_new' => $isNew,
                    'weight' => $weight,
                ]);
            }

            activity()
                ->performedOn(new RFQSection())
                ->causedBy(Auth::user())
                ->log('Assigned sections to RFQ ID: ' . $rfqId);

            DB::commit();
            return back()->with('success', 'RFQ Evaluation sections saved successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new RFQSection())
                ->causedBy(Auth::user())
                ->log('Failed to assign sections to RFQ ID: ' . $rfqId . ' Error: ' . $th->getMessage());

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
        $section->ModifiedBy = Auth::id();
        $section->ModifiedOn = now();
        $section->save();

        activity()
            ->performedOn($section)
            ->causedBy(Auth::user())
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

        $section->DeletedBy = Auth::id();
        $section->save();

        activity()
            ->performedOn($section)
            ->causedBy(Auth::user())
            ->withProperties(['section_name' => $sectionName])
            ->log('Deleted RFQ section: ' . $sectionName);

        $section->delete();

        return redirect()->back()->with('success', 'RFQ section deleted successfully.');
    }
}
