<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemCategories;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Fetch all suppliers from the database
        $suppliers = Supplier::with('category')->orderBy('CreatedOn', 'desc')->paginate(20);

        $categories = ItemCategories::all();

        // Return the view with the suppliers data
        return view('procurement.suppliers.index', compact('suppliers', 'categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::with('category')->findOrFail($id);
        return view('procurement.suppliers.show', compact('supplier'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Find the supplier by ID
        $supplier = Supplier::findOrFail($id);

        // Return the view for editing the supplier
        return view('procurement.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request data
        $request->validate([
            'SupplierName' => 'required|string|max:255',
            'ContactEmail' => 'nullable|email|max:255',
            'ContactPhone' => 'nullable|string|max:20',
            'Address' => 'nullable|string|max:255',
        ]);

        // Find the supplier by ID and update it
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        // Redirect to the suppliers index with a success message
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the supplier by ID and delete it
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        // Redirect to the suppliers index with a success message
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
