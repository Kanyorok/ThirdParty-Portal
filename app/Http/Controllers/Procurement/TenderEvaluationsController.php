<?php

namespace App\Http\Controllers\procurement;

use App\Http\Controllers\Controller;
use App\Models\procurement\Section;
use App\Models\Procurement\Tender;
use Illuminate\Http\Request;

class TenderEvaluationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return Tender::all();
        $tenders=Tender::select('Id','TenderNo','Title')->get();
        $sections=Section::select('Id','SectionName')->get();
        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.tenderevaluations',compact(
            'tenders',
            'sections'
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
}
