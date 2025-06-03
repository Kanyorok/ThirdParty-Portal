<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\Procurement\Item;
use App\Services\Procurement\ProcurementPlan\DepartmentNeedsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DepartmentNeedsController extends Controller
{
    protected DepartmentNeedsService $service;

    public function __construct(DepartmentNeedsService $service)
    {
        $this->service = $service;
    }

    public function create()
    {
        $items = Item::with('category','itemuom')->orderBy('ItemName')->get();
        return view('procurement.procurementplan.departmentneeds.raiseneed.create', compact('items'));
    }

    public function store(Request $request, DepartmentNeedsService $service)
    {
        //dd($request->all());

        try {
            DB::transaction(function () use ($request, $service) {
                $actor = $request->user();
                $service->create($request->all(), $actor);
            });

            return redirect()->route('procurementdepartmentalplan.index')->with('success', 'Department need created!');
        } catch (Throwable $e) {
            Log::error("--- CREATE DEPARTMENT NEEDS ERROR --- " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create need.']);
        }

    }

    public function index(Request $request)
    {
        $departmentneedviews = DepartmentNeeds::with('creator')->where('Status', 'p')->get();
        return view('procurement.procurementplan.departmentneeds.raiseneed.index', compact('departmentneedviews'));
    }

    public function fetchLinesByDPlan($NeedID)
    {
        $lines = DepartmentNeeds::with('item')
            ->where('NeedID', $NeedID)
            ->get()
            ->map(function ($line) {
                return [
                    'id' => $line->id,
                    'NeedID' => $line->NeedID,
                    'ItemID' => $line->ItemID,
                    'ItemName' => $line->item->ItemName ?? 'Unknown',
                    'RequestedQty' => $line->RequestedQty,
                    'EstimatedUnitCost' => $line->EstimatedUnitCost,
                    'Status' => $line->Status,
                    'FiscalYear' => $line->FiscalYear,
                ];
            });

        return response()->json($lines);
    }

    public function update(Request $request)
    {
        foreach ($request->Needs as $needData) {
            $need = DepartmentNeeds::where('NeedID', $needData['NeedID'])->firstOrFail();

            $need->update([
                'RequestedQty' => $needData['RequestedQty'],
                'EstimatedUnitCost' => $needData['EstimatedUnitCost'],
                'FiscalYear' => $needData['FiscalYear'],
                'ModifiedOn' => now(),
                'ModifiedBy' => auth()->id(),
            ]);
        }

        return redirect()->route('procurementdepartmentalplan.index')->with('success', 'Needs updated successfully.');
    }


    public function destroy($NeedID)
    {
        try {
            $needs = DepartmentNeeds::where('NeedID', $NeedID)->get();

            foreach ($needs as $need) {
                $need->delete();
            }

            return redirect()->route('procurementdepartmentalplan.index')->with('success', 'Department need deleted.');
        } catch (Throwable $e) {
            Log::error("--- DELETE DEPARTMENT NEED ERROR --- " . $e->getMessage());
            return redirect()->route('procurementdepartmentalplan.index')->withErrors(['error' => 'Failed to delete department need.']);
        }
    }


}
