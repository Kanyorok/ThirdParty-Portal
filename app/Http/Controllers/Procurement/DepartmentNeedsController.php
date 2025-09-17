<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\DepartmentNeed;
use App\Services\Procurement\ProcurementPlan\DepartmentNeedsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $items = ItemMasterList::with(['category', 'uom', 'price'])
            ->whereNotNull('ItemPrice')
            ->whereHas('price', function ($q) {
                $q->whereNotNull('ActualPrice')->where('ActualPrice', '>', 0);
            })
            ->orderBy('ItemName')
            ->get();
        return view('procurement.procurementplan.departmentneeds.raiseneed.create', compact('items'));
    }

    public function store(Request $request, DepartmentNeedsService $service)
    {
        try {
            // Basic validation: estimated cost must be present and > 0
            $validated = $request->validate([
                'ItemID' => ['required', 'integer', 'exists:t_Items,Id'],
                'RequestedQty' => ['required', 'numeric', 'min:1'],
                'EstimatedUnitCost' => ['required', 'numeric', 'gt:0'],
                'RequestedDate' => ['required', 'date'],
                'Justification' => ['nullable', 'string'],
            ]);

            // Guard: ensure the chosen item has an estimated/actual price configured and > 0
            $hasValidPrice = \App\Models\Inventory\ItemMasterList::query()
                ->where('Id', $validated['ItemID'])
                ->whereNotNull('ItemPrice')
                ->whereHas('price', function ($q) {
                    $q->whereNotNull('ActualPrice')
                      ->where('ActualPrice', '>', 0);
                })
                ->exists();

            if (!$hasValidPrice) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['ItemID' => 'Cannot raise a need for an item without a configured estimated cost.']);
            }

            DB::transaction(function () use ($request, $service) {
                $actor = $request->user();
                $service->create($request->all(), $actor);
            });

            return redirect()->route('procurementdepartmentalplan.index')
                ->with('success', 'Department need created!');
        } catch (\Exception $e) {
            Log::error("--- CREATE DEPARTMENT NEEDS ERROR --- " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        $departmentneedviews = DepartmentNeed::with('creator')->where('Status', DepartmentNeedsEnum::Pending)->get();
        return view('procurement.procurementplan.departmentneeds.raiseneed.index', compact('departmentneedviews'));
    }

    public function fetchLinesByDPlan($NeedID)
    {
        $lines = DepartmentNeed::with('item')
            ->where('NeedID', $NeedID)
            ->get()
            ->map(function ($line) {
                return [
                    'id' => $line->id,
                    'NeedID' => $line->NeedID,
                    'ItemID' => $line->ItemID,
                    'ItemName' => $line->item->ItemName ?? 'Unknown',
                    'ItemCode' => $line->item->ItemCode ?? 'Unknown',
                    'item' => $line->item ? [
                        'ItemCode' => $line->item->ItemCode ?? 'Unknown',
                        'ItemName' => $line->item->ItemName ?? 'Unknown',
                    ] : null,
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
            $need = DepartmentNeed::where('NeedID', $needData['NeedID'])->firstOrFail();

            $need->update([
                'RequestedQty' => $needData['RequestedQty'],
                'EstimatedUnitCost' => $needData['EstimatedUnitCost'],
                'FiscalYear' => $needData['FiscalYear'],
                'ModifiedOn' => now(),
                'ModifiedBy' => Auth::id(),
            ]);
        }

        return redirect()->route('procurementdepartmentalplan.index')->with('success', 'Needs updated successfully.');
    }


    public function destroy($NeedID)
    {
        try {
            $needs = DepartmentNeed::where('NeedID', $NeedID)->get();

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
