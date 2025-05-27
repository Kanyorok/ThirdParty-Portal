<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQLine;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $rfqs = RFQ::with(['category', 'suppliers'])->get();
        return view('procurement.rfqs.index', compact('rfqs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ItemCategories::all();
        $suppliers = Supplier::all(); // Fetch all suppliers for selection
        return view('procurement.rfqs.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Comments' => 'nullable|string|max:255',
            'SubmissionDeadline' => 'required|date|after:today',
        ]);

        // Fetch items + quantities for this category
        // $items = DB::table('t_RequisitionLines as rl')
        //     ->join('t_Items as i', 'rl.Item', '=', 'i.id')
        //     ->where('rl.CategoryId', $request->ItemCategoryId)
        //     ->select('i.Name as name', 'rl.Quantity as quantity', 'i.UOM as uom', 'rl.Description as description')
        //     ->get();

        // Check if no items are found
        // if ($items->isEmpty()) {
        //     return redirect()->back()->with('warning', 'No items requisitioned with the chosen category.');
        // }

        // Format items for JSON storage


        $prefix = 'RFQ-';
        $lastRFQ = RFQ::where('RFQNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQ ? intval(substr($lastRFQ->RFQNumber, strlen($prefix))) : 0;
        $newRFQNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        // Create the RFQ
        $rfq = RFQ::create([
            'RFQNumber' => $newRFQNumber,
            'Comments' => $request->Comments,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
            'Status' => 'Pending',
        ]);

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ created with requisition items and sent to suppliers.');
    }

    /**
     * Approve the RFQ and notify suppliers.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:t_Suppliers,Id',
        ]);

        $rfq = RFQ::findOrFail($id);

        // Update RFQ status to Approved
        $rfq->update(['Status' => 'Approved']);

        // Update supplier statuses in the pivot table
        $rfq->suppliers()->syncWithPivotValues($request->suppliers, ['Status' => 'Approved']);

        // Notify selected suppliers
        // $suppliers = Supplier::whereIn('Id', $request->suppliers)->get(['SupplierName', 'ContactEmail']);
        // foreach ($suppliers as $supplier) {
             //todo @mureithi send email to supplier
        // }

        return redirect()->back()->with('success', 'RFQ has been approved and emails sent to selected suppliers.');
    }

    /**
     * Reject the RFQ.
     */
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
    public function show($id)
    {
        // Get the RFQ and its associated RFQLines
        $rfq = RFQ::with('rfqLines')->findOrFail($id);
        
        // Get unique itemCategoryIds from the RFQLines
        $itemCategoryIds = $rfq->rfqLines->pluck('ItemCategoryId')->unique();

        // Fetch suppliers whose CategoryId matches any of the itemCategoryIds
        $suppliers = Supplier::whereIn('CategoryId', $itemCategoryIds)->get();

        return view('procurement.rfqs.show', compact('rfq', 'suppliers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rfq = RFQ::with('suppliers')->findOrFail($id);
        $categories = ItemCategories::all();
        $suppliers = Supplier::all();

        return view('procurement.rfqs.edit', compact('rfq', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ItemCategoryId' => 'required|exists:t_ItemCategories,id',
            'Comments' => 'nullable|string|max:255',
            'SubmissionDeadline' => 'required|date|after:today',
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:t_Suppliers,Id',
        ]);

        $rfq = RFQ::findOrFail($id);

        // Update RFQ details
        $rfq->update([
            'ItemCategoryId' => $request->ItemCategoryId,
            'Comments' => $request->Comments,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        // Update suppliers in the pivot table
        $rfq->suppliers()->sync($request->suppliers);

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rfq = RFQ::findOrFail($id);
        $rfq->delete();

        return redirect()->route('rfqs.index')->with('success', 'RFQ deleted successfully.');
    }
}
