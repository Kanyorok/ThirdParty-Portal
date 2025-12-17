<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\RFQLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        
        if (!$rfq || !$rfq->RequisitionId) {
            return redirect()->back()->with('error', 'Invalid RFQ or no requisition linked.');
        }

        // Step 3: Load ONLY requisition lines from THIS specific requisition
        $requisitionlines = RequisitionLine::with('item.category')
            ->where('RequisitionID', $rfq->RequisitionId) // ✅ Filter by specific requisition
            ->whereNull('DeletedOn') // Exclude deleted lines
            ->get();

        // Step 4: Filter requisition lines
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
            $itemCategoryId = $request->query('itemCategoryId') ?? $request->query('ItemCategoryId');

            // Resolve column names for t_ItemCategories (id, name, parent)
            $catTable = 't_ItemCategories';
            $catIdCol = collect(['Id','ID','id'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Id';
            $catNameCol = collect(['Name','CategoryName','name'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Name';
            $catParentCol = collect(['ParentId','ParentID','parent_id'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'ParentId';

            // Resolve column names for t_SupplierCategory_ItemCategory
            $scicTable = 't_SupplierCategory_ItemCategory';
            $scicItemCol = collect(['ItemCategoryID','ItemCategoryId','item_category_id'])
                ->first(fn($c) => Schema::hasColumn($scicTable, $c)) ?? 'ItemCategoryID';
            $scicSupCol  = collect(['SupplierCategoryID','SupplierCategoryId','supplier_category_id'])
                ->first(fn($c) => Schema::hasColumn($scicTable, $c)) ?? 'SupplierCategoryID';

            // Get ONLY categories from THIS specific requisition (not already in RFQ lines)
            $categories = DB::table('t_RequisitionLines as rl')
                ->join('t_Items as i', 'rl.Item', '=', 'i.Id')
                ->join($catTable . ' as c', 'i.Category', '=', DB::raw("c.$catIdCol"))
                ->leftJoin('t_RFQLines as rfql', 'rl.Id', '=', 'rfql.RequisitionLineId')
                ->where('rl.RequisitionID', $requisitionId) // Filter by specific requisition
                ->whereNull('rl.DeletedOn')
                ->whereNull('i.DeletedOn')
                ->whereNull('rfql.Id') //  Exclude categories already fully added to RFQ
                ->select(DB::raw("c.$catIdCol as Id"), DB::raw("c.$catNameCol as Name"))
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

            // Add ancestors (treat NULL/0 as root terminators)
            foreach ($baseIds as $baseId) {
                $baseId = (int)$baseId;
                $parent = DB::table($catTable)->where($catIdCol, $baseId)->value($catParentCol);
                while (!is_null($parent) && (int)$parent !== 0) {
                    $pid = (int)$parent;
                    $allCategoryIds->push($pid);
                    $parent = DB::table($catTable)->where($catIdCol, $pid)->value($catParentCol);
                }
            }

            // Add descendants (BFS traversal)
            $queue = collect($baseIds)->map(fn($v) => (int)$v);
            while ($queue->isNotEmpty()) {
                $currentBatch = $queue->splice(0, 100)->all();
                $children = DB::table($catTable)
                    ->whereIn($catParentCol, $currentBatch)
                    ->pluck($catIdCol)
                    ->map(fn($v) => (int)$v);
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
            $supQuery = DB::table('t_Suppliers as s')
                ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
                    $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
                })
                ->whereNull('s.DeletedOn')
                ->whereNull('tp.DeletedOn')
                ->where('s.Active_Status', 1)
                ->where(function ($outer) use ($allCategoryIds, $scicTable, $scicItemCol, $scicSupCol) {
                    // Direct: supplier CategoryId mapped to item category
                    $outer->whereExists(function ($q) use ($allCategoryIds, $scicTable, $scicItemCol, $scicSupCol) {
                        $q->select(DB::raw(1))
                          ->from($scicTable . ' as scic')
                          ->whereNull('scic.DeletedOn')
                          ->whereIn("scic.$scicItemCol", $allCategoryIds)
                          ->whereColumn("scic.$scicSupCol", 's.CategoryId');
                    });

                    // Pivot: through t_ThirdParty_SupplierCategory (PascalCase columns)
                    if (Schema::hasTable('t_ThirdParty_SupplierCategory')
                        && Schema::hasColumn('t_ThirdParty_SupplierCategory', 'ThirdPartyID')
                        && Schema::hasColumn('t_ThirdParty_SupplierCategory', 'SupplierCategoryID')) {
                        $outer->orWhereExists(function ($q) use ($allCategoryIds, $scicTable, $scicItemCol, $scicSupCol) {
                            $q->select(DB::raw(1))
                              ->from('t_ThirdParty_SupplierCategory as tpsc')
                              ->join($scicTable . ' as scic', "scic.$scicSupCol", '=', 'tpsc.SupplierCategoryID')
                              ->whereNull('scic.DeletedOn')
                              ->whereIn("scic.$scicItemCol", $allCategoryIds)
                              ->whereColumn('tpsc.ThirdPartyID', 'tp.Id');
                        });
                    }

                    // Pivot: snake_case columns
                    if (Schema::hasTable('t_ThirdParty_SupplierCategory')
                        && Schema::hasColumn('t_ThirdParty_SupplierCategory', 'third_party_id')
                        && Schema::hasColumn('t_ThirdParty_SupplierCategory', 'supplier_category_id')) {
                        $outer->orWhereExists(function ($q) use ($allCategoryIds, $scicTable, $scicItemCol, $scicSupCol) {
                            $q->select(DB::raw(1))
                              ->from('t_ThirdParty_SupplierCategory as tpsc2')
                              ->join($scicTable . ' as scic2', "scic2.$scicSupCol", '=', 'tpsc2.supplier_category_id')
                              ->whereNull('scic2.DeletedOn')
                              ->whereIn("scic2.$scicItemCol", $allCategoryIds)
                              ->whereColumn('tpsc2.third_party_id', 'tp.Id');
                        });
                    }
                })
                ->select(
                    's.Id as SupplierId',
                    's.CategoryId as SupplierCategoryId',
                    'tp.Id as ThirdPartyId',
                    'tp.TradingName',
                    'tp.BusinessType',
                    DB::raw('COALESCE(tpu.Email, tp.Email) as Email')
                )
                ->distinct();

            $suppliers = $supQuery->get();

            // Optional diagnostic logging
            try {
                Log::info('RFQ requisition categories resolution', [
                    'rfqRequisitionId' => $requisitionId,
                    'cat_cols' => ['id' => $catIdCol, 'name' => $catNameCol, 'parent' => $catParentCol],
                    'scic_cols' => ['item' => $scicItemCol, 'supplier' => $scicSupCol],
                    'baseIds' => $baseIds,
                    'allIds' => $allCategoryIds,
                    'suppliers_count' => $suppliers->count(),
                ]);
            } catch (\Throwable $e) { /* no-op */ }

            return response()->json([
                'categories' => $categories,
                'suppliers' => $suppliers,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}