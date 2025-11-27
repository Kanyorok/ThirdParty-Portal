<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\CommissionRuleRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Services\Insurance\CommissionRuleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Insurance\BancassuranceCommissionRule;
use App\Models\Insurance\InsuranceProduct;

class CommissionRuleController extends Controller
{
    public function create()
    {
        $this->authorize(PermissionEnum::CommissionRuleView, BancassuranceCommissionRule::class);
        $rules = BancassuranceCommissionRule::all();
        $products = InsuranceProduct::all();
        $policytypes = CodeDetail::where('CodeID', 'PolicyTypeId')->get();
        $assignto = CodeDetail::where('CodeID', 'AppliesTo')->get();

        return view('bancassurance.commissions.rules.create', compact('rules', 'policytypes', 'assignto', 'products'));
    }

    public function store(CommissionRuleRequest $request)
    {
        $this->authorize(PermissionEnum::CommissionRuleCreate, BancassuranceCommissionRule::class);
        $validated = $request->validated();

        $ProductId = InsuranceProduct::findOrFail($validated['ProductId']);
        $PolicyTypeId = CodeDetail::findOrFail($validated['PolicyTypeId']) ?? null;
        $AppliesTo = CodeDetail::findOrFail($validated['AppliesTo']) ?? null;

        $rule = CommissionRuleService::create(
            $validated['RuleName'],
            $ProductId,
            $PolicyTypeId,
            $validated['CommissionRate'],
            $validated['FixedAmount'],
            $AppliesTo,
            $validated['IsActive'] ?? '',
            Auth::user(),
        );

        return redirect()->route('commissions.rules.index')->with('success', 'Commission rule created.');
    }

    public function index()
    {
        $rules = BancassuranceCommissionRule::all();
        $policytypes = CodeDetail::where('CodeID', 'PolicyTypeId')->get();
        $assignto = CodeDetail::where('CodeID', 'AppliesTo')->get();
        return view('bancassurance.commissions.rules.index', compact('rules', 'policytypes', 'assignto'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::CommissionRuleView, BancassuranceCommissionRule::class);
        $rule = BancassuranceCommissionRule::findOrFail($id);
        $products = InsuranceProduct::all();
        $policytypes = CodeDetail::where('CodeID', 'PolicyTypeId')->get();
        $assignto = CodeDetail::where('CodeID', 'AppliesTo')->get();
        return view('bancassurance.commissions.rules.edit', compact('rule', 'products', 'policytypes', 'assignto'));
    }

    public function update(CommissionRuleRequest $request, $id)
    {
        $this->authorize(PermissionEnum::CommissionRuleUpdate, BancassuranceCommissionRule::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $rule = BancassuranceCommissionRule::findOrFail($id);

            $rule->update([
                'RuleName' => $validated['RuleName'],
                'ProductId' => $validated['ProductId'],
                'PolicyTypeId' => $validated['PolicyTypeId'],
                'CommissionRate' => $validated['CommissionRate'],
                'FixedAmount' => $validated['FixedAmount'],
                'AppliesTo' => $validated['AppliesTo'],
                'IsActive' => $validated['IsActive'] ?? '',
                'ModifiedBy' => Auth::id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($rule)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated rule');

            return redirect()->route('commissions.rules.index')->with('success', 'Rule updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update rule:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update rule'])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::CommissionRuleDelete, BancassuranceCommissionRule::class);
        try {
            $rule = BancassuranceCommissionRule::findOrFail($id);
            $rule->delete();

            return redirect()->route('commissions.rules.index')
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
