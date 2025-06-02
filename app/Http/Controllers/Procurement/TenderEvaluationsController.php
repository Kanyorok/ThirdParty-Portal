<?php

namespace App\Http\Controllers\procurement;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\Section;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCriteria;
use App\Models\Procurement\TenderSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenderEvaluationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return Tender::all();
        $sections=Section::select('id','SectionName')->get();
        //Get unique tenderID form the TenderSection table
        $tenderSections = TenderSection::select('TenderID')->distinct()->get();
        //Get tender that are not in the TenderSection table
        $tenderIds = $tenderSections->pluck('TenderID')->toArray();
        //return Tender::whereNotIn('Id', $tenderIds)->get();
        //Get tenders that are not in the TenderSection table
        $tenders = Tender::whereNotIn('Id', $tenderIds)->get();
        //Get tender that are have sections
        $tenderswithsections = Tender::whereIn('Id', $tenderIds)->get();

        $data=[];
        foreach ($tenderswithsections as $key => $value) {
            array_push($data,[
                'id'=>$value->Id,
                'TenderNo'=>$value->TenderNo,
                'Title'=>$value->Title,
                'sectionsNumber'=>TenderSection::where('TenderID',$value->Id)->count(),
                'criteriaNumber'=>TenderCriteria::where('TenderID',$value->Id)
                    ->where('IsActive',true)
                    ->count(),
            ]);
        }
        //return$data;
        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.tenderevaluations',compact(
            'tenders',
            'sections',
            'data'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    //Store Sections associated to a tender
    public function tenderSections(Request $request){
        //check if user has permission to create tender sections
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);
        // Validate the request data
        $request->validate([
            'tender_id' => 'required',
            'sections' => 'required',
            'weights' => 'required|array',
            'weights.*' => 'numeric|min:0|max:100',
        ]);
        $tenderTitle=$request->tender_id;
        $sections=$request->sections;
        $weights=$request->weights;
        // Check if the total weight is 100
        $totalWeight = array_sum($weights);
        // if ($totalWeight !== 100) {
        //     return back()->with('error', 'The total weight must be 100.');
        // }
        DB::beginTransaction();  
        try {
        // Loop through each section and add with its weight
        foreach ($sections as $index => $sectionId) {
            // Check if the section exists
            $section = Section::find($sectionId);
            if (!$section) {
                return back()->with('error', 'Section with ID ' . $sectionId . ' does not exist.');
            }
            // Create or update the tender section
             TenderSection::create([
                'TenderID' => $request->tender_id, // Assuming tender_id is passed in the request
                'SectionID' => $sectionId,
                'Weight' => $weights[$index],
                'IsActive' => true, // Assuming sections are active by default
                'Comments' => $request->comments[$index] ?? null, // Optional comments
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ]);
            
        }
        DB::commit();
        // Log the action
        activity()
            ->performedOn(new Tender())
            ->causedBy(auth()->id())
            ->log('Created or updated tender sections for tender: ' . $tenderTitle);
        // Return a success response
        return back()->with('success', 'Tender sections created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            // Log the error
            activity()
                ->performedOn(new Tender())
                ->causedBy(auth()->id())
                ->log('Failed to create tender sections: ' . $th->getMessage());
            // Return an error response
            return back()->with('error', 'Failed to create tender sections: ');
        }
    }

    //Get Criteria associated to a tender section
    public function getTenderCriteria(Request $request, $TenderId)
    {
        $tender = Tender::findOrFail($TenderId);

        // Fetch all active criteria already stored for this tender
        $existingCriteria = TenderCriteria::where('TenderID', $TenderId)
            ->where('IsActive', true)
            ->pluck('CriteriaID')
            ->toArray();

        // Get sections associated with the tender along with their criteria
        $tenderSections = TenderSection::where('TenderID', $TenderId)
            ->with(['sections', 'criteria']) // eager load
            ->get();

        // Add isChecked to each criterion
        foreach ($tenderSections as $section) {
            foreach ($section->criteria as $criteria) {
                $criteria->isChecked = in_array($criteria->id, $existingCriteria);
            }
        }

        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.tenderCriteria', compact(
            'TenderId',
            'tender',
            'tenderSections'
        ));
    }


public function storeTenderCriteria(Request $request)
{
    $this->authorize(PermissionEnum::TenderWrite, Tender::class);
    $validated = $request->validate([
        'TenderId' => 'required|integer',
        'weights' => 'required|array',
        'criterias' => 'nullable|array',
    ]);

    $tenderId = $validated['TenderId'];
    $weights = $validated['weights'];
    $criterias = $validated['criterias'] ?? [];

    try {
        DB::beginTransaction();

        // Loop through each section and its selected criterias
        foreach ($weights as $sectionId => $weight) {
            $selectedCriteria = $criterias[$sectionId] ?? [];

            // Get all criteria IDs for this section (from the definitions table)
            $allSectionCriteria = Criteria::where('SectionID', $sectionId)->pluck('id');

            foreach ($allSectionCriteria as $criteriaId) {
                $isSelected = in_array($criteriaId, $selectedCriteria);

                TenderCriteria::updateOrCreate(
                    [
                        'TenderID' => $tenderId,
                        'CriteriaID' => $criteriaId,
                    ],
                    [
                        'SectionID' => $sectionId,
                        'MaxScore' => 10, // Set weight if selected, otherwise 0
                        'IsActive' => $isSelected,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]
                );
            }
        }

        DB::commit();
        // Log the action
        activity()
            ->performedOn(new Tender())
            ->causedBy(Auth::id())
            ->log('Saved tender criteria for tender ID: ' . $tenderId);

        return redirect()->back()->with('success', 'Tender criteria saved successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return $e->getMessage();
        Log::error('Error saving tender criteria', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return redirect()->back()->with('error', 'An error occurred. Please try again.');
    }
}

}
