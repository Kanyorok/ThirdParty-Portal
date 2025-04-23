<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\Tender;
use App\Models\Procurement\ItemCategory;
use App\Models\Procurement\Supplier;
use Illuminate\Support\Facades\Mail;

class RFQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rfqs = RFQ::with(['tender', 'category'])->get();
        return view('procurement.rfqs.index', compact('rfqs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usedTenderIds = RFQ::pluck('TenderId')->toArray();
        $tenders = Tender::whereNotIn('Id', $usedTenderIds)
                     ->whereNotIn('Status', ['cancelled', 'closed'])
                     ->get();

        $categories = ItemCategory::all();
        return view('procurement.rfqs.create', compact('tenders', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'TenderId' => 'required|exists:t_Tenders,id',
            'ItemCategoryId' => 'required|exists:t_ItemCategories,id',
        ]);

        // Get suppliers in the selected category
        $suppliers = Supplier::where('CategoryId', $request->ItemCategoryId)->get();

        // Send emails (replace with actual email logic)
        foreach ($suppliers as $supplier) {
            Mail::raw("You have a new RFQ for tender.", function ($message) use ($supplier) {
                $message->to($supplier->ContactEmail)
                        ->subject('RFQ Invitation');
            });
        }

        // Save RFQ
        $rfq = RFQ::create([
            'TenderId' => $request->TenderId,
            'ItemCategoryId' => $request->ItemCategoryId,
            'Suppliers' => $suppliers->pluck('Id')->toArray(),
        ]);

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ sent to suppliers!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rfq = RFQ::with(['tender', 'category'])->findOrFail($id);
        $suppliers = Supplier::whereIn('Id', $rfq->Suppliers)->get();

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
