<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartiesBankDetails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class ThirdPartiesBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ThirdParties $parties): JsonResponse
    {
        try {
            $query = ThirdParties::query()
                ->with(['businessType:Id,Description', 'country:Id,Name,Flag', 'types:TypeId,Code,Description', 'status:Id,Description']);

            return DataTables::of($query)->editColumn('types', function (ThirdParties $thirdParties) {
                return $thirdParties->types->pluck('Description')->map(fn ($type) => "<span class='badge bg-primary'>{$type}</span>")->implode(' ');
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                'dbl_click_url' => function (ThirdParties $thirdParties) {
                    return route('thirdparty.parties.show', $thirdParties->Id);
                },
            ])->addIndexColumn()->rawColumns(['types'])->make();
        } catch (\Throwable $e) {
            Log::error('Failed to load third parties: ' . $e->getMessage());

            return $this->errored('unexpected error occurred while loading the data. please try again later.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
    public function show(ThirdPartiesBankDetails $thirdPartiesBankDetails)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ThirdPartiesBankDetails $thirdPartiesBankDetails)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThirdPartiesBankDetails $thirdPartiesBankDetails)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThirdPartiesBankDetails $thirdPartiesBankDetails)
    {
    }
}
