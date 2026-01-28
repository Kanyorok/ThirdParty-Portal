<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassuranceCommissionPayoutRequest;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Insurance\BancassuranceCommissionPayout;
use App\Models\Insurance\BancassuranceCommissionRule;
use App\Models\Insurance\BancassurancePolicy;
use App\Services\Insurance\BancassuranceCommissionPayoutService;
use Illuminate\Support\Facades\Auth;

class CommissionPayoutController extends Controller
{
    public function index()
    {
        $paymentmodes = CodeDetail::where('CodeID', 'PaymentMode')->get();
        $policies = BancassurancePolicy::all();
        $payouts = BancassuranceCommissionPayout::all();


        return view('bancassurance.commissions.payouts.index', compact('payouts', 'policies', 'paymentmodes'));
    }

    public function pay()
    {
        $paymentmodes = CodeDetail::where('CodeID', 'PaymentModes')->get();
        $policies = BancassurancePolicy::all();
        $payout = BancassuranceCommissionPayout::all();
        $currencies = Currency::all();
        $commissionRules = BancassuranceCommissionRule::all();
        $PaidTo = User::all();

        return view('bancassurance.commissions.payouts.pay', compact('payout', 'policies', 'paymentmodes', 'PaidTo', 'currencies', 'commissionRules'));
    }

    public function store(BancassuranceCommissionPayoutRequest $request)
    {
        $validated = $request->validated();

        $PolicyId = BancassurancePolicy::findOrFail($validated['PolicyId']);
        $CurrencyId = Currency::findOrFail($validated['CurrencyId']);
        $CommissionRuleId = BancassuranceCommissionRule::findOrFail($validated['CommissionRuleId']);
        $PaymentMode = CodeDetail::findOrFail($validated['PaymentMode']);
        $PaidTo = Auth::user();
        $PaymentDate = new \DateTime($validated['PaymentDate']);

        $customer = BancassuranceCommissionPayoutService::create(
            $PolicyId,
            $validated['PayoutReference'],
            $validated['PaidAmount'],
            $CurrencyId,
            $CommissionRuleId,
            $PaymentDate,
            $PaymentMode,
            $validated['Remarks'],
            $PaidTo,
            Auth::user(),
        );

        return redirect()->route('bancassurance.commissions.payouts.index')->with('success', 'Commission payout created successfully.');
    }
}
