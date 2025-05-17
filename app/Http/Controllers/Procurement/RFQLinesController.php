<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\RFQLines;
use Illuminate\Http\Request;

class RFQLinesController extends Controller
{
    public function create()
    {
        // Logic to create RFQ lines
        $requisitions = Requisitions::all(); // Fetch all requisitions for selection

        return view('procurement.rfqlines.create', compact('requisitions'));
    }

    public function getCategories($id)
    {
        $requisition = Requisitions::with('requisitionLines.item.category')->findOrFail($id);

        // Get unique item categories from items in requisition lines
        $categories = $requisition->requisitionLines
            ->map(fn($line) => $line->item->category)
            ->unique('Id')
            ->values();

        return response()->json($categories);
    }

}
