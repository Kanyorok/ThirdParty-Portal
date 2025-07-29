<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceTaxType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceTaxTypeController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingView, FinanceTaxType::class);
        
        // Fetch all tax types from the database
         $taxTypes = FinanceTaxType::all();

        // Return the view with the tax types data
        return view('finance.taxmanagement.taxtypes.index', compact('taxTypes'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingCreate, FinanceTaxType::class);
        // Return the view to create a new tax type
        return view('finance.taxmanagement.taxtypes.create');
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingCreate, FinanceTaxType::class);
        // Validate and store the new tax type
        $validated = $request->validate([
            'TaxTypeName' => 'required|string|unique:t_FinanceTaxType,TaxTypeName',
            'Description' => 'nullable|string',
        ]);

        $exists = FinanceTaxType::where('TaxTypeName', $validated['TaxTypeName'])->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['TaxTypeName' => 'Tax Type already exists.']);
        }

        DB::beginTransaction();
        try {

        $type = FinanceTaxType::create([
            'TaxTypeName' => $validated['TaxTypeName'],
            'Description' => $validated['Description'],
            'CreatedBy' => Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        activity()
            ->performedOn($type)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'create'])
            ->log('Tax Type created');

            DB::commit();
        return redirect()->route('taxtypes.index')->with('success', 'Tax Type created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error or handle it as needed
            Log::error('Failed to create Tax Type: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);
            // Redirect back with an error message  
            return redirect()->back()->withErrors(['error' => 'Failed to create Tax Type: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingUpdate, FinanceTaxType::class);
        // Find the tax type by ID
        $taxType = FinanceTaxType::findOrFail($id);

        // Return the view to edit the tax type
        return view('finance.taxmanagement.taxtypes.edit', compact('taxType'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingUpdate, FinanceTaxType::class);
        // Validate and update the tax type
        $validated = $request->validate([
            'TaxTypeName' => 'required|string|unique:t_FinanceTaxType,TaxTypeName,' . $id,
            'Description' => 'nullable|string',
        ]);

        $exists = FinanceTaxType::where('TaxTypeName', $validated['TaxTypeName'])
            ->where('Id', '!=', $id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()->withErrors(['TaxTypeName' => 'Tax Type already exists.']);
        }

        DB::beginTransaction();
        try {
            $taxType = FinanceTaxType::findOrFail($id);
            $taxType->update([
                'TaxTypeName' => $validated['TaxTypeName'],
                'Description' => $validated['Description'],
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($taxType)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Tax Type updated');

            DB::commit();
            return redirect()->route('taxtypes.index')->with('success', 'Tax Type updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Tax Type: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to update Tax Type: ' . $e->getMessage()]);
        }
    }
    
    public function destroy(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingDelete, FinanceTaxType::class);

        DB::beginTransaction();
        try {
            $taxType = FinanceTaxType::findOrFail($id);
            $taxType->DeletedBy = Auth::id();
            $taxType->save();
            // Soft delete the tax type 
            $taxType->delete();

            activity()
                ->performedOn($taxType)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Tax Type deleted');

            DB::commit();
            return redirect()->route('taxtypes.index')->with('success', 'Tax Type deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Tax Type: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete Tax Type: ' . $e->getMessage()]);
        }
    }
}