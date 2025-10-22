<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\RFQLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFQLinesController extends Controller
{
    public function create($rfqId)
    {
        // Get the linked requisition for this RFQ
        $requisition = DB::table('t_Requisition')
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
        ]);

        // Step 2: Load requisition lines with related items and categories
        $requisitionlines = RequisitionLine::with('item.category')->get();

        // Step 3: Filter requisition lines
        $filteredItems = $requisitionlines->filter(function ($line) use ($request) {
            // Check if the item belongs to the selected category
            $isInCategory = $line->item
                && $line->item->category
                && $line->item->category->Id == $request->ItemCategoryId;

            // Check if this exact RequisitionLine is already used in RFQ lines
            $existsInRFQLines = RFQLine::where('RequisitionLineId', $line->Id)->exists();

            // Exclude if already associated
            return $isInCategory && !$existsInRFQLines;
        });

        // Step 4: If no matching items, redirect with warning
        if ($filteredItems->isEmpty()) {
            return redirect()->back()->with('warning', 'No items requisitioned with the chosen category.');
        }

        // Step 5: Create RFQ lines
        $prefix = 'RFQL-';
        $lastRFQ = RFQLine::where('RFQLineNo', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQ ? intval(substr($lastRFQ->RFQLineNo, strlen($prefix))) : 0;

        $counter = $lastNumber;
        foreach ($filteredItems as $line) {
            $counter++;
            $rfqLineNumber = $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);

            RFQLine::create([
                'RFQLineNo' => $rfqLineNumber,
                'RequisitionLineId' => $line->Id, // updated field
                'RequisitionId' => $line->RequisitionID,
                'ItemCategoryId' => $request->ItemCategoryId,
                'RFQId' => $request->RFQId,
                'ItemId' => $line->Item,
                'ItemName' => $line->item->ItemName,
                'Quantity' => intval($line->Quantity),
                'UOM' => $line->item->UOM ?? '',
                'CreatedBy' => \Illuminate\Support\Facades\Auth::user()->Id,
                'ModifiedBy' => \Illuminate\Support\Facades\Auth::user()->Id,
            ]);
        }

        return redirect()->route('rfqs.show', $request->RFQId)->with('success', 'RFQ line(s) created successfully.');
    }

    public function getRequisitionCategories(Request $request, $requisitionId)
    {
        try {
            $itemCategoryId = $request->query('itemCategoryId') ?? $request->query('ItemCategoryId');

            // Base categories present on requisition lines (do not exclude those already tied to RFQ lines)
            $categories = DB::table('t_RequisitionLines as rl')
                ->join('t_Items as i', 'rl.Item', '=', 'i.Id')
                ->join('t_ItemCategories as c', 'i.Category', '=', 'c.Id')
                ->where('rl.RequisitionID', $requisitionId)
                ->select('c.Id', 'c.Name')
                ->distinct()
                ->get();

            // Build a comprehensive set of category IDs including:
            // - the base categories on the requisition
            // - all ancestor categories (so parent mappings qualify)
            // - all descendant categories (so if parent is mapped, its children are also covered)
            $allCategoryIds = collect();
            // If a specific ItemCategoryId is provided, use it as the base
            if (!empty($itemCategoryId)) {
                $baseIds = collect([(int)$itemCategoryId]);
            } else {
                $baseIds = $categories->pluck('Id')->unique()->values();
            }

            // Add base
            $allCategoryIds = $allCategoryIds->merge($baseIds);

            // Add ancestors
            foreach ($baseIds as $baseId) {
                $parent = DB::table('t_ItemCategories')->where('Id', $baseId)->value('ParentId');
                while ($parent) {
                    $allCategoryIds->push($parent);
                    $parent = DB::table('t_ItemCategories')->where('Id', $parent)->value('ParentId');
                }
            }

            // Add descendants (BFS traversal)
            $queue = collect($baseIds);
            while ($queue->isNotEmpty()) {
                $currentBatch = $queue->splice(0, 100)->all();
                $children = DB::table('t_ItemCategories')
                    ->whereIn('ParentId', $currentBatch)
                    ->pluck('Id');
                $newChildren = $children->diff($allCategoryIds);
                if ($newChildren->isNotEmpty()) {
                    $allCategoryIds = $allCategoryIds->merge($newChildren);
                    $queue = $queue->merge($newChildren);
                }
            }

            $allCategoryIds = $allCategoryIds->unique()->values();

            // Prepare a subquery to fetch a single contact email per third party (prefer any available)
            $thirdPartyUserEmailSub = DB::table('t_ThirdPartyUsers as tpu')
                ->select('tpu.ThirdPartyId', DB::raw('MIN(tpu.Email) as Email'))
                ->whereNull('tpu.DeletedOn')
                ->groupBy('tpu.ThirdPartyId');

            // Suppliers (ThirdParty details) whose classification encompasses any of the categories set
            $suppliers = DB::table('t_Suppliers as s')
                ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
                    $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
                })
                ->whereNull('s.DeletedOn')
                ->whereNull('tp.DeletedOn')
                ->where('s.Active_Status', 1)
                ->whereExists(function ($q) use ($allCategoryIds) {
                    $q->select(DB::raw(1))
                        ->from('t_SupplierCategory_ItemCategory as scic')
                        ->join('t_SupplierCategories as sc', 'sc.SupplierCategoryID', '=', 'scic.SupplierCategoryID')
                        ->whereNull('sc.DeletedOn')
                        ->whereNull('scic.DeletedOn')
                        ->whereIn('scic.ItemCategoryID', $allCategoryIds)
                        ->whereColumn('sc.SupplierCategoryID', 's.CategoryId');
                })
                ->select(
                    's.Id as SupplierId',
                    's.CategoryId as SupplierCategoryId',
                    'tp.Id as ThirdPartyId',
                    'tp.TradingName',
                    'tp.BusinessType',
                    DB::raw('COALESCE(tpu.Email, tp.Email) as Email')
                )
                ->distinct()
                ->get();

            return response()->json([
                'categories' => $categories,
                'suppliers' => $suppliers,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
