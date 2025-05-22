<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\RFQLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RFQLinesController extends Controller
{
    public function create()
    {
        return view('procurement.rfqlines.create');
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
                'CreatedBy' => auth()->user()->Id,
                'ModifiedBy' => auth()->user()->Id,
            ]);
        }

        return redirect()->route('rfqs.show', $request->RFQId)->with('success', 'RFQ line(s) created successfully.');
    }

    public function getCategories()
    {
        Log::info('getCategories() was called');
        // Get all requisition lines with item and its category
        $lines = RequisitionLine::with('item.category')->get();

        // Extract categories from items, avoiding nulls
        $categories = $lines
            ->map(fn($line) => $line->item?->category)
            ->filter() // remove nulls
            ->unique('Id') // or 'id', based on your DB
            ->values();

        return response()->json($categories);
    }


}
