<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQSettingCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFQSettingCriteriaController extends Controller
{
    public function index()
    {
        return redirect()->back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'section_id' => 'required|integer|exists:t_RFQSettingSections,id',
        ]);

        DB::beginTransaction();

        try {
            RFQSettingCriteria::create([
                'CriteriaName' => $request->input('name'),
                'SectionID' => $request->input('section_id'),
                'Description' => $request->input('desc', null),
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ]);

            activity()
                ->performedOn(new RFQSettingCriteria())
                ->causedBy(auth()->id())
                ->log('Created a new RFQ criteria: ' . $request->input('name'));

            DB::commit();

            return back()->with('success', 'Criteria created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            activity()
                ->performedOn(new RFQSettingCriteria())
                ->causedBy(auth()->id())
                ->log('Error creating RFQ criteria: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create criteria: ' . $th->getMessage()]);
        }
    }

    public function show(string $id)
    {
        $sectionID = $id;
        $criterias = RFQSettingCriteria::where('SectionID', $id)->get();

        return view('procurement.rfqs.settings.criterias', compact('criterias', 'sectionID'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        $criteria = RFQSettingCriteria::findOrFail($id);
        $oldValues = $criteria->getOriginal();

        $criteria->CriteriaName = $request->name;
        $criteria->Description = $request->desc;
        $criteria->ModifiedBy = auth()->id();
        $criteria->ModifiedOn = now();
        $criteria->save();

        activity()
            ->performedOn($criteria)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldValues,
                'new' => $criteria->getChanges(),
            ])
            ->log('Updated RFQ criteria: ' . $criteria->CriteriaName);

        return redirect()->back()->with('success', 'Criteria updated successfully.');
    }

    public function destroy(string $id)
    {
        $criteria = RFQSettingCriteria::findOrFail($id);
        $criteriaName = $criteria->CriteriaName;

        $criteria->DeletedBy = auth()->id();
        $criteria->save();

        activity()
            ->performedOn($criteria)
            ->causedBy(auth()->user())
            ->withProperties(['criteria_name' => $criteriaName])
            ->log('Deleted RFQ criteria: ' . $criteriaName);

        $criteria->delete();

        return redirect()->back()->with('success', 'Criteria deleted successfully.');
    }
}
