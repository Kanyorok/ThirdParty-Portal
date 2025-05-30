<?php

namespace App\Http\Controllers\procurement;

use App\Http\Controllers\Controller;
use App\Models\procurement\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return 11;
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
