<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Log;
use Throwable;
use Yajra\DataTables\DataTables;

class ThirdPartyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            /*
                       <th>Name</th>ThirdPartyName
                           <th>ID / Reg No.</th> RegistrationNumber
                           <th>Type</th> businessType.Description
                           <th>Profiles</th>types
                           <th>Country</th>country.Name
                           <th>Status</th> status.Description

                      */

            try {
                $query = ThirdParties::query()->with(['businessType:Id,Description', 'country:Id,Name,Flag', 'types', 'status:Id,Description']);

                return DataTables::of($query)->addIndexColumn()->make();
            } catch (Throwable $e) {
                Log::error('Failed to load third parties: ' . $e->getMessage());
                return $this->errored('unexpected error occurred while loading the data. please try again later.');
            }


        }
        return view('thirdparty.index', [
            'types' => ThirdPartyType::query()->get(['Code', 'Description'])
        ]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ThirdParties $thirdParties)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ThirdParties $thirdParties)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThirdParties $thirdParties)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThirdParties $thirdParties)
    {
        //
    }
}
