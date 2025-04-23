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

        // 1. Fetch suppliers
        $suppliers = Supplier::where('CategoryId', $request->ItemCategoryId)->get();

        // 2. Fetch items + quantities for this category
        $items = DB::table('t_RequisitionLines as rl')
            ->join('t_Items as i', 'rl.Item', '=', 'i.id')
            ->where('rl.CategoryId', $request->ItemCategoryId)
            ->select('i.Name as name', 'rl.Quantity as quantity')
            ->get();

        // 3. Format items for JSON storage
        $requisitionItems = $items->map(function ($item) {
            return [
                'name' => $item->name,
                'quantity' => $item->quantity,
            ];
        });


        // 4. Create the RFQ
        $rfq = RFQ::create([
            'TenderId' => $request->TenderId,
            'ItemCategoryId' => $request->ItemCategoryId,
            'Suppliers' => $suppliers->pluck('Id')->toArray(),
            'RequisitionItems' => $requisitionItems,
        ]);

        // 5. Notify suppliers
        foreach ($suppliers as $supplier) {
            Mail::raw("You have a new RFQ for tender.", function ($message) use ($supplier) {
                $message->to($supplier->ContactEmail)
                        ->subject('RFQ Invitation');
            });
        }

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ created with requisition items and sent to suppliers.');
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
