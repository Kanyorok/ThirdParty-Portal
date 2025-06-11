<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\User;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections= Section::all();
        return view('procurement.tendering.settings.sections',compact('sections'));
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
        //check if the user has permission to create a section
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create a new section
            Section::create([
                'SectionName' => $request->input('name'),
                'Description' => $request->input('desc', null), // Default to null if not provided
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);
            //Log the action
            activity()
                ->performedOn(new Section())
                ->causedBy(Auth::id())
                ->log('Created a new section: ' . $request->input('name'));
            DB::commit();
            // Return a success response
            return back()->with(
                'success',
                'Section created successfully: '
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            // Log the error
            activity()
                ->performedOn(new Section())
                ->causedBy(Auth::id())
                ->log('Failed to create section: ' . $th->getMessage());
            // Return an error response
            return back()->with(
                'error',
                'Failed to create section: ' . $th->getMessage()
            );
        }

        return $request->all();
    }
  

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        $section = Section::findOrFail($id);
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
            ->log('Updated section: ' . $section->SectionName);

        return redirect()->back()->with('success', 'Section updated successfully.');
    }

    
    public function destroy(string $id)
    {
        $section = Section::findOrFail($id);
        $sectionName = $section->SectionName;

        $section->DeletedBy = auth()->id(); 
        $section->save();

        activity()
            ->performedOn($section)
            ->causedBy(auth()->user())
            ->withProperties(['section_name' => $sectionName])
            ->log('Deleted section: ' . $sectionName);

        $section->delete();

        return redirect()->back()->with('success', 'Section deleted successfully.');
    }
}