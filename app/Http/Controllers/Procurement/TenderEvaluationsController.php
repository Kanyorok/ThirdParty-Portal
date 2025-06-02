<?php

namespace App\Http\Controllers\procurement;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\procurement\Section;
use App\Models\Procurement\Tender;
use App\Models\procurement\TenderSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'criteriaNumber'=>2,
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
}
