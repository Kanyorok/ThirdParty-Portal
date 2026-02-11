<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\RFQLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFQLinesController extends Controller
{
    public function create($rfqId)
    {
        // Get the linked requisition for this RFQ
        $requisition = DB::table('t_Requisitions')
            ->where('Id', function ($query) use ($rfqId) {
                $query->select('RequisitionId')
                    ->from('t_RFQ')
                    ->where('Id', $rfqId);
            })
            ->first();

        return view('procurement.rfqlines.create', [
            'rfqId' => $rfqId,
            'requisition' => $requisition,
        ]);
    }

    public function store(Request $request)
    {
        // Step 1: Validate request
        $validatedData = $request->validate([
            'ItemCategoryId' => 'required|exists:t_ItemCategories,Id',
            'RFQId' => 'required|exists:t_RFQ,Id',
        ]);

        // Step 2: Get the requisition ID from the RFQ
        $rfq = DB::table('t_RFQ')->where('Id', $request->RFQId)->first();

        if (! $rfq || ! $rfq->RequisitionId) {
            return redirect()->back()->with('error', 'Invalid RFQ or no requisition linked.');
        }

        // Step 3: Load ONLY requisition lines from THIS specific requisition
        $requisitionlines = RequisitionLine::with('item.category')
            ->where('RequisitionID', $rfq->RequisitionId) //  Filter by specific requisition
            ->whereNull('DeletedOn') // Exclude deleted lines
            ->get();

        // Step 4: Filter requisition lines
        $filteredItems = $requisitionlines->filter(function ($line) use ($request) {
            // Check if the item belongs to the selected category
            $isInCategory = $line->item
                && $line->item->category
                && $line->item->category->Id == $request->ItemCategoryId;

            // Check if this exact RequisitionLine is already used in RFQ lines
            $existsInRFQLines = RFQLine::where('RequisitionLineId', $line->Id)
    ->whereHas('rfq', function ($query) {
        $query->whereIn('Status', ['Ap', 'Pe']);
    })
    ->exists();

            // Exclude if already associated
            return $isInCategory && ! $existsInRFQLines;
        });

        // Step 5: If no matching items, redirect with warning
        if ($filteredItems->isEmpty()) {
            return redirect()->back()->with('warning', 'No items requisitioned with the chosen category or all items already added to RFQ.');
        }

        // Step 6: Group items by ItemId and sum quantities (in case same item appears multiple times)
        $groupedItems = $filteredItems->groupBy('Item')->map(function ($group) {
            $firstItem = $group->first();

            return [
                'RequisitionLineIds' => $group->pluck('Id')->toArray(), // Store all line IDs
                'ItemId' => $firstItem->Item,
                'ItemName' => $firstItem->item->ItemName,
                'TotalQuantity' => $group->sum('Quantity'), // Sum quantities
                'UOM' => $firstItem->item->UOM ?? '',
                'RequisitionID' => $firstItem->RequisitionID,
            ];
        });

        // Step 7: Create RFQ lines
        $prefix = 'RFQL-';
        $lastRFQ = RFQLine::where('RFQLineNo', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQ ? intval(substr($lastRFQ->RFQLineNo, strlen($prefix))) : 0;

        $counter = $lastNumber;
        $createdCount = 0;

        foreach ($groupedItems as $item) {
            $counter++;
            $rfqLineNumber = $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);

            // Create one RFQ line per unique item (with consolidated quantity)
            RFQLine::create([
                'RFQLineNo' => $rfqLineNumber,
                'RequisitionLineId' => $item['RequisitionLineIds'][0], // Use first line ID as reference
                'RequisitionId' => $item['RequisitionID'],
                'ItemCategoryId' => $request->ItemCategoryId,
                'RFQId' => $request->RFQId,
                'ItemId' => $item['ItemId'],
                'ItemName' => $item['ItemName'],
                'Quantity' => intval($item['TotalQuantity']), // Use consolidated quantity
                'UOM' => $item['UOM'],
                'CreatedBy' => \Illuminate\Support\Facades\Auth::user()->Id,
                'ModifiedBy' => \Illuminate\Support\Facades\Auth::user()->Id,
            ]);

            $createdCount++;
        }

        return redirect()->route('rfqs.show', $request->RFQId)
            ->with('success', "RFQ line(s) created successfully. Added {$createdCount} item(s) from requisition.");
    }

    public function getRequisitionCategories(Request $request, $requisitionId)
    {
        try {
            Log::info('getRequisitionCategories called', [
                'requisitionId' => $requisitionId,
            ]);

            // Get distinct item categories from requisition lines
            $categories = DB::table('t_RequisitionLines as rl')
                ->join('t_Items as i', 'rl.Item', '=', 'i.Id')
                ->join('t_ItemCategories as ic', 'i.Category', '=', 'ic.Id')
                ->where('rl.RequisitionID', $requisitionId)
                ->whereNull('rl.DeletedOn')
                ->whereNull('i.DeletedOn')
                ->whereNull('ic.DeletedOn')


                // Exclude lines that are already in ACTIVE RFQs
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('t_RFQLines as rfql')
                    ->join('t_RFQ as rfq', 'rfql.RFQId', '=', 'rfq.Id')
                    ->whereColumn('rfql.RequisitionLineId', 'rl.Id')
                    ->whereNull('rfq.DeletedOn')
                    ->whereIn('rfq.Status', ['Approved', 'Pending']);

            })

            ->select('ic.Id', 'ic.Name')
            ->distinct()
            ->get();


            Log::info('Categories fetched', [
                'requisitionId' => $requisitionId,
                'count' => $categories->count(),
            ]);

            return response()->json([
                'success' => true,
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getRequisitionCategories', [
                'requisitionId' => $requisitionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories',
                'categories' => [],
            ], 500);
        }
    }
}
