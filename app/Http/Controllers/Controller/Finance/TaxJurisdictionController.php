<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\TaxJurisdiction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaxJurisdictionController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingView, TaxJurisdiction::class);

        $taxJurisdictions = TaxJurisdiction::with(['currency:Id,Code'])->get();
        return view('finance.taxmanagement.taxjurisdictions.index', compact('taxJurisdictions'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingCreate, TaxJurisdiction::class);
        // Fetch currencies for the dropdown
        $currencies = Currency::all();

        return view('finance.taxmanagement.taxjurisdictions.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingCreate, TaxJurisdiction::class);
        // Validate and store the tax jurisdiction data
        $validated = $request->validate([
            'JurisdictionName' => 'required|string|unique:t_FinanceTaxJurisdiction,JurisdictionName',
            'Currency' => 'required|exists:t_Currencies,Id',
            'TaxAuthority' => 'required|string|max:255',
        ]);
        DB::beginTransaction();
        try {
            $taxJurisdiction = TaxJurisdiction::create([
                'JurisdictionName' => $validated['JurisdictionName'],
                'Currency'=> $validated['Currency'],
                'TaxAuthority' => $validated['TaxAuthority'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($taxJurisdiction)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created tax jurisdiction: ' . $taxJurisdiction->JurisdictionName);
                
            DB::commit();  

            return redirect()->route('taxjurisdiction.index')->with('success', 'Tax Jurisdiction created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating tax jurisdiction: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create tax jurisdiction. ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingUpdate, TaxJurisdiction::class);
        // Fetch the tax jurisdiction for editing
        $taxJurisdiction = TaxJurisdiction::findOrFail($id);
        $currencies = Currency::all();

        return view('finance.taxmanagement.taxjurisdictions.edit', compact('taxJurisdiction', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingUpdate, TaxJurisdiction::class);
        // Validate and update the tax jurisdiction data
        $validated = $request->validate([
            'JurisdictionName' => 'required|string|unique:t_FinanceTaxJurisdiction,JurisdictionName,' . $id,
            'Currency' => 'required|exists:t_Currencies,Id',
            'TaxAuthority' => 'required|string|max:255',
        ]);
        DB::beginTransaction();
        try {
            $taxJurisdiction = TaxJurisdiction::findOrFail($id);
            $taxJurisdiction->update([
                'JurisdictionName' => $validated['JurisdictionName'],
                'Currency'=> $validated['Currency'],
                'TaxAuthority' => $validated['TaxAuthority'],
                'Status' => $request->has('Status') ? true : false,
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($taxJurisdiction)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated tax jurisdiction: ' . $taxJurisdiction->JurisdictionName);

            DB::commit();

            return redirect()->route('taxjurisdiction.index')->with('success', 'Tax Jurisdiction updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating tax jurisdiction: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update tax jurisdiction. ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingDelete, TaxJurisdiction::class);
        // Delete the tax jurisdiction
        DB::beginTransaction();
        try {
            $taxJurisdiction = TaxJurisdiction::findOrFail($id);
            $taxJurisdiction-> DeletedBy = Auth::id();
            $taxJurisdiction->save();
            $taxJurisdiction->delete();

            activity()
                ->performedOn($taxJurisdiction)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted tax jurisdiction: ' . $taxJurisdiction->JurisdictionName);

            DB::commit();

            return redirect()->route('taxjurisdiction.index')->with('success', 'Tax Jurisdiction deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting tax jurisdiction: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => request()->all(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete tax jurisdiction. ' . $e->getMessage()]);
        }
    }
}

