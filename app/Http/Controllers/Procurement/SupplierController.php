<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all suppliers from the database
        $suppliers = Supplier::orderBy('created_at', 'desc')->paginate(20);

        // Return the view with the suppliers data
        return view('procurement.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return the view for creating a new supplier
        return view('procurement.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'SupplierName' => 'required|string|max:255',
            'ContactEmail' => 'nullable|email|max:255',
            'ContactPhone' => 'nullable|string|max:20',
            'Address' => 'nullable|string|max:255',
            'IsPrequalified' => 'boolean',
        ]);

        // Create a new supplier
        Supplier::create($request->all());

        // Redirect to the suppliers index with a success message
        return redirect()->route('procurement.suppliers.index')->with('success', 'Supplier created successfully.');
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
            'IsPrequalified' => 'boolean',
        ]);

        // Find the supplier by ID and update it
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        // Redirect to the suppliers index with a success message
        return redirect()->route('procurement.suppliers.index')->with('success', 'Supplier updated successfully.');
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
        return redirect()->route('procurement.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
