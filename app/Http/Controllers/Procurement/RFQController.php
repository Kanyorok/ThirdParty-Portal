<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\Tender;
use App\Models\Procurement\ItemCategory;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\RFQ;

class RFQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenders = Tender::all();
        $categories = ItemCategory::all();
        return view('procurement.rfqs.create', compact('tenders', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'TenderId' => 'required|exists:tenders,id',
            'ItemCategoryId' => 'required|exists:item_categories,id',
            'SupplierIds' => 'required|array',
            'SupplierIds.*' => 'exists:suppliers,id'
        ]);

        foreach ($request->SupplierIds as $supplierId) {
            RFQ::create([
                'TenderId' => $request->TenderId,
                'ItemCategoryId' => $request->ItemCategoryId,
                'SupplierId' => $supplierId,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(), 
            ]);
        }

        return redirect()->back()->with('success', 'RFQs created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

    public function getSuppliersByCategory(Request $request)
    {
        $suppliers = Supplier::whereHas('category', function ($q) use ($request) {
            $q->where('CategoryId', $request->category_id);
        })->get();

        return response()->json($suppliers);
    }
}
