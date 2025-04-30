<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\Tender;
use App\Models\Procurement\ItemCategory;
use Illuminate\Support\Facades\DB;
use App\Models\Procurement\Supplier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class RFQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rfqs = RFQ::with(['category'])->get();
        return view('procurement.rfqs.index', compact('rfqs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ItemCategory::all();
        return view('procurement.rfqs.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ItemCategoryId' => 'required|exists:t_ItemCategories,id',
            'Comments' => 'nullable|string|max:255',
            'SubmissionDeadline' => 'required|date|after:today',
        ]);

        // Fetch items + quantities for this category
        $items = DB::table('t_RequisitionLines as rl')
            ->join('t_Items as i', 'rl.Item', '=', 'i.id')
            ->where('rl.CategoryId', $request->ItemCategoryId)
            ->select('i.Name as name', 'rl.Quantity as quantity', 'i.UOM as uom', 'rl.Description as description')
            ->get();

        // Check if no items are found
        if ($items->isEmpty()) {
            return redirect()->back()->with('warning', 'No items requisitioned with the chosen category.');
        }

        // Format items for JSON storage
        $requisitionItems = $items->map(function ($item) {
            return [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit' => $item->uom,
                'description' => $item->description,
            ];
        });

        $prefix = 'RFQ-';
        $lastRFQ = RFQ::where('RFQNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQ ? intval(substr($lastRFQ->RFQNumber, strlen($prefix))) : 0;
        $newRFQNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        // Create the RFQ
        $rfq = RFQ::create([
            'RFQNumber' => $newRFQNumber,
            'ItemCategoryId' => $request->ItemCategoryId,
            'RequisitionItems' => $requisitionItems,
            'Comments' => $request->Comments,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
            'Status' => 'Pending',
        ]);

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ created with requisition items and sent to suppliers.');
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:t_Suppliers,Id',
        ]);

        $rfq = RFQ::findOrFail($id);

        // Fetch supplier details
        $suppliers = Supplier::whereIn('Id', $request->suppliers)->get(['SupplierName', 'ContactEmail']);
        // Update RFQ status to Approved and store supplier details
        $rfq->update([
            'Status' => 'Approved',
            'Suppliers' => $suppliers->toJson(), // Store supplier details as JSON
        ]);

        // Notify selected suppliers
        foreach ($suppliers as $supplier) {
            Mail::raw("You have a new RFQ for tender.", function ($message) use ($supplier) {
                $message->to($supplier->ContactEmail)
                        ->subject('RFQ Invitation');
            });
        }

        return redirect()->back()->with('success', 'RFQ has been approved and emails sent to selected suppliers.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'RejectionReason' => 'required|string|max:255',
        ]);

        $rfq = RFQ::findOrFail($id);
        $rfq->update([
            'Status' => 'Rejected',
            'Comments' => $request->RejectionReason,
        ]);

        return redirect()->back()->with('success', 'RFQ has been rejected successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function getRequisitionItems($rfqId)
    {
        // Fetch the RFQ by ID
        $rfq = RFQ::find($rfqId);

        // Check if the RFQ exists
        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        // Check if the RequisitionItems field exists
        if (!$rfq->RequisitionItems) {
            return response()->json(['error' => 'No requisition items found'], 404);
        }

        // Decode the RequisitionItems JSON field only if it's a string
        $requisitionItems = is_string($rfq->RequisitionItems)
            ? json_decode($rfq->RequisitionItems, true)
            : $rfq->RequisitionItems;

        // Check if decoding was successful
        if (is_string($rfq->RequisitionItems) && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in RequisitionItems'], 500);
        }

        // Return the requisition items as JSON
        return response()->json([
            'requisitionItems' => $requisitionItems,
        ]);
    }

    public function show($id)
    {
        $rfq = RFQ::with(['category'])->findOrFail($id);

        // Get suppliers based on the RFQ's category
        $suppliers = Supplier::where('CategoryId', $rfq->ItemCategoryId)->get();

        return view('procurement.rfqs.show', compact('rfq', 'suppliers'));
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
