<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\ProviderAndProducts\InsurancePricingRuleRequest;
use App\Models\Core\Currency;
use App\Models\Insurance\InsurancePricingRule;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\ProviderAndProducts\InsurancePricingRuleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PricingRuleController extends Controller
{
    public function index()
    {
        $rules = InsurancePricingRule::all();

        return view('bancassurance.pricing.index', compact('rules'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::InsurancePricingRuleView, InsurancePricingRule::class);
        $providers = InsuranceProvider::all();
        $products = InsuranceProduct::all();
        $currencies = Currency::all();


        return view('bancassurance.pricing.create', compact('providers', 'products', 'currencies'));
    }

    public function store(InsurancePricingRuleRequest $request)
    {
        $this->authorize(PermissionEnum::InsurancePricingRuleCreate, InsurancePricingRule::class);
        $validated = $request->validated();

        $InsuranceProviderId = InsuranceProvider::findOrFail($validated['InsuranceProviderId']);
        $Product = InsuranceProduct::findOrFail($validated['Product']);
        $CurrencyId = Currency::findOrFail($validated['CurrencyId']);

        $providers = InsurancePricingRuleService::create(
            $InsuranceProviderId,
            $Product,
            $validated['RuleName'],
            $validated['CoverageAmountMax'],
            $validated['CoverageAmountMin'],
            $validated['PremiumRate'],
            $CurrencyId,
            $validated['AgeMin'],
            $validated['AgeMax'],
            $validated['TenureMin'],
            $validated['TenureMax'],
            $validated['IsActive'],
            Auth::user(),
        );

        return redirect()->route('bancassurance.pricing.index')->with('success', 'Pricing rule saved successfully.');
    }

    public function getProductByProvider($providerId)
    {
        $products = InsuranceProduct::where('InsuranceProviderID', $providerId)->get();

        return response()->json($products);
    }

    public function edit($Id)
    {
        $this->authorize(PermissionEnum::InsurancePricingRuleView, InsurancePricingRule::class);

        $rule = InsurancePricingRule::findOrFail($Id);
        $providers = InsuranceProvider::all();
        $currencies = Currency::all();

        return view('bancassurance.pricing.edit', compact('rule', 'providers', 'currencies'));
    }

    // Update product
    public function update(InsurancePricingRuleRequest $request, $id)
    {
        $this->authorize(PermissionEnum::InsurancePricingRuleUpdate, InsurancePricingRule::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $rule = InsurancePricingRule::findOrFail($id);
            $CurrencyId = Currency::findOrFail($validated['CurrencyId']);

            $rule->update([
                'InsuranceProviderId' => $validated['InsuranceProviderId'],
                'Product' => $validated['Product'],
                'RuleName' => $validated['RuleName'],
                'CoverageAmountMax' => $validated['CoverageAmountMax'],
                'CoverageAmountMin' => $validated['CoverageAmountMin'],
                'PremiumRate' => $validated['PremiumRate'],
                'CurrencyId' => $CurrencyId->Id,
                'AgeMin' => $validated['AgeMin'],
                'AgeMax' => $validated['AgeMax'],
                'TenureMin' => $validated['TenureMin'],
                'TenureMax' => $validated['TenureMax'],
                'IsActive' => $validated['IsActive'],
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($rule)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Rule ');

            return redirect()->route('bancassurance.pricing.index')->with('success', 'Rule updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Rule:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update Rule'])->withInput();
        }
    }

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::InsurancePricingRuleDelete, InsurancePricingRule::class);

        try {
            $rule = InsurancePricingRule::findOrFail($Id);
            $rule->delete();

            return redirect()->route('bancassurance.pricing.index')
                ->with('success', 'Rule Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Rule: ' . $th->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Rule. Please try again.'])
                ->withInput();
        }
    }
}
