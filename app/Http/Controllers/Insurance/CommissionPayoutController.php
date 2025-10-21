<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceCommissionPayout;
use App\Models\Insurance\BancassurancePolicy;
use App\Http\Requests\Insurance\BancassuranceCommissionPayoutRequest;
use App\Services\Insurance\BancassuranceCommissionPayoutService;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CommissionPayoutController extends Controller
{
    public function index()
    {
        $paymentmodes = CodeDetail::where('CodeID', 'PaymentMode')->get();
        $policies = BancassurancePolicy::all();
        $payouts = BancassuranceCommissionPayout::all();

        return view('bancassurance.commissions.payouts.index', compact('payouts','policies', 'paymentmodes'));
    }
    public function pay()
    {
        $paymentmodes = CodeDetail::where('CodeID', 'PaymentModes')->get();
        $policies = BancassurancePolicy::all();
        $payout = BancassuranceCommissionPayout::all();
        $paidBy = User::all();
        return view('bancassurance.commissions.payouts.pay', compact('payout','policies', 'paymentmodes', 'paidBy'));

    }
   public function store(BancassuranceCommissionPayoutRequest $request)
    {
        //$this->authorize(PermissionEnum::BancassuranceCustomersCreate, BancassuranceCustomer::class);
        $validated = $request->validated();

        $PolicyId = BancassurancePolicy::findOrFail($validated['PolicyId']);
        $PaymentMode = CodeDetail::findOrFail($validated['PaymentMode']);
        $PaidBy = Auth::user();
        $PaymentDate = new \DateTime($validated['PaymentDate']);

        $customer = BancassuranceCommissionPayoutService::create(
                $PolicyId,
                $validated['PayoutReference'],
                $validated['PaidAmount'],
                $PaymentDate,
                $PaymentMode, 
                $validated['Remarks'],
                $PaidBy,
                Auth::user(),
            );

        return redirect()->route('bancassurance.commissions.payouts.index')->with('success', 'Commission payout created successfully.');
    }
}
