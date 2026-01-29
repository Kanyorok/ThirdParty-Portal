<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\Finance\FinanceTaxType;
use App\Models\Finance\TaxJurisdiction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaxRuleController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingView, FinanceTaxRuleConfiguration::class);

        $taxRule = FinanceTaxRuleConfiguration::with([
            'taxType:Id,TaxTypeName',
            'jurisdiction:Id,JurisdictionName',
            'taxPayableGLAccount:Id,GLCode,GLName',
            'taxReceivableGLAccount:Id,GLCode,GLName',
        ])->get();

        return view('finance.taxmanagement.taxruleconfiguration.index', compact('taxRule'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingCreate, FinanceTaxRuleConfiguration::class);

        $taxTypes = FinanceTaxType::all();
        $jurisdictions = TaxJurisdiction::all();
        $glAccounts = FinanceGLAccounts::all();

        return view('finance.taxmanagement.taxruleconfiguration.create', compact('taxTypes', 'jurisdictions', 'glAccounts'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingCreate, FinanceTaxRuleConfiguration::class);
        // Validate and store the tax rule configuration
        $validated = $request->validate([
            'TaxTypeId' => 'required|exists:t_FinanceTaxType,Id',
            'JurisdictionId' => 'required|string|max:100',
            'Rate' => 'required|numeric',
            'AppliesTo' => 'required|string',
            'ThresholdAmount' => 'nullable|numeric',
            'ApplyTaxPer' => 'required|string',
            'EffectiveFrom' => 'required|date',
            'EffectiveTo' => 'nullable|date|after_or_equal:EffectiveFrom',
            'TaxPayableGLID' => 'required|exists:t_FinanceGLAccounts,Id',
            'TaxReceivableGLID' => 'required|exists:t_FinanceGLAccounts,Id',
        ]);



        DB::beginTransaction();

        try {
            $taxRule = FinanceTaxRuleConfiguration::create([
                'TaxTypeId' => $validated['TaxTypeId'],
                'JurisdictionId' => $validated['JurisdictionId'],
                'Rate' => $validated['Rate'],
                'AppliesTo' => $validated['AppliesTo'],
                'ThresholdAmount' => $validated['ThresholdAmount'] ?? 0,
                'ApplyTaxPer' => $validated['ApplyTaxPer'],
                'EffectiveFrom' => $validated['EffectiveFrom'],
                'EffectiveTo' => $validated['EffectiveTo'] ?? null,
                'TaxPayableGLID' => $validated['TaxPayableGLID'],
                'TaxReceivableGLID' => $validated['TaxReceivableGLID'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($taxRule)
                ->causedBy(Auth::user())
                ->log('Created a new tax rule configuration.');

            DB::commit();

            return redirect()->route('taxruleconfig.index')->with('success', 'Tax Rule Configuration created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Creating Tax Rule' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to create tax rule configuration: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingUpdate, FinanceTaxRuleConfiguration::class);

        $taxRule = FinanceTaxRuleConfiguration::findOrFail($id);
        $taxTypes = FinanceTaxType::all();
        $jurisdictions = TaxJurisdiction::all();
        $glAccounts = FinanceGLAccounts::all();

        return view('finance.taxmanagement.taxruleconfiguration.edit', compact('taxRule', 'taxTypes', 'jurisdictions', 'glAccounts'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingUpdate, FinanceTaxRuleConfiguration::class);

        // Validate and update the tax rule configuration
        $validated = $request->validate([
            'TaxTypeId' => 'required|exists:t_FinanceTaxType,Id',
            'JurisdictionId' => 'required|string|max:100',
            'Rate' => 'required|numeric',
            'AppliesTo' => 'required|string',
            'ThresholdAmount' => 'nullable|numeric',
            'ApplyTaxPer' => 'required|string',
            'EffectiveFrom' => 'required|date',
            'EffectiveTo' => 'nullable|date|after_or_equal:EffectiveFrom',
            'TaxPayableGLID' => 'required|exists:t_FinanceGLAccounts,Id',
            'TaxReceivableGLID' => 'required|exists:t_FinanceGLAccounts,Id',
            // 'Status' => 'required|boolean',
        ]);



        DB::beginTransaction();

        try {
            $taxRule = FinanceTaxRuleConfiguration::findOrFail($id);
            $taxRule->update([
                'TaxTypeId' => $validated['TaxTypeId'],
                'JurisdictionId' => $validated['JurisdictionId'],
                'Rate' => $validated['Rate'],
                'AppliesTo' => $validated['AppliesTo'],
                'ThresholdAmount' => $validated['ThresholdAmount'] ?? 0,
                'ApplyTaxPer' => $validated['ApplyTaxPer'],
                'EffectiveFrom' => $validated['EffectiveFrom'],
                'EffectiveTo' => $validated['EffectiveTo'] ?? null,
                'TaxPayableGLID' => $validated['TaxPayableGLID'],
                'TaxReceivableGLID' => $validated['TaxReceivableGLID'],
                'Status' => $request->has('Status') ? true : false, // Default to true if not provided
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($taxRule)
                ->causedBy(Auth::user())
                ->log('Updated tax rule configuration.');

            DB::commit();

            return redirect()->route('taxruleconfig.index')->with('success', 'Tax Rule Configuration updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Updating Tax Rule: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to update tax rule configuration: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceTaxSettingDelete, FinanceTaxRuleConfiguration::class);

        DB::beginTransaction();

        try {
            $taxRule = FinanceTaxRuleConfiguration::findOrFail($id);
            $taxRule->DeletedBy = Auth::Id();
            $taxRule->save();
            $taxRule->delete();

            activity()
                ->performedOn($taxRule)
                ->causedBy(Auth::user())
                ->log('Deleted tax rule configuration.');

            DB::commit();

            return redirect()->route('taxruleconfig.index')->with('success', 'Tax Rule Configuration deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Deleting Tax Rule: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to delete tax rule configuration: ' . $e->getMessage()]);
        }
    }
}
