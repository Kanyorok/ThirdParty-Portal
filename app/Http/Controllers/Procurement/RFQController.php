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

        // 2. Fetch items + quantities for this category
        $items = DB::table('t_RequisitionLines as rl')
            ->join('t_Items as i', 'rl.Item', '=', 'i.id')
            ->where('rl.CategoryId', $request->ItemCategoryId)
            ->select('i.Name as name', 'rl.Quantity as quantity', 'i.UOM as uom', 'rl.Description as description')
            ->get();

        // 3. Format items for JSON storage
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

        // 1. Generate RFQ number
        $rfqNumber = $newRFQNumber;

        // 4. Create the RFQ
        $rfq = RFQ::create([
            'RFQNumber' => $rfqNumber,
            'ItemCategoryId' => $request->ItemCategoryId,
            'RequisitionItems' => $requisitionItems,
            'Comments' => $request->Comments,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
            'Status' => 'Pending',
        ]);

        // 5. Notify suppliers
        // foreach ($suppliers as $supplier) {
        //     Mail::raw("You have a new RFQ for tender.", function ($message) use ($supplier) {
        //         $message->to($supplier->ContactEmail)
        //                 ->subject('RFQ Invitation');
        //     });
        // }

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ created with requisition items and sent to suppliers.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rfq = RFQ::with(['category'])->findOrFail($id);
        return view('procurement.rfqs.show', compact('rfq'));
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
