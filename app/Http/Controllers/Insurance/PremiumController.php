<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Insurance\InsurancePolicyStatus;
use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Insurance\PremiumManagement\BancassurancePremiumPaymentsRequest;
use Illuminate\Support\Facades\Log;
use App\Models\Insurance\BancassurancePolicy;
use App\Services\Insurance\PremiumManagement\BancassurancePremiumPaymentsService;
use App\Models\Core\Approval\CodeDetail;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassurancePremiumPayments;

class PremiumController extends Controller
{
    //
    public function create()
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsView, BancassurancePremiumPayments::class);
        $payment = BancassurancePremiumPayments::all();
        $policies = BancassurancePolicy::where('Status', InsurancePolicyStatus::Issued)->get();
        $balances = [];
        $currencies = Currency::all();
        $filteredPolicies = $policies->filter(function ($policy) use (&$balances) {
            $totalPaid = BancassurancePremiumPayments::where('PolicyID', $policy->Id)->sum('Amount');
            $riderPremium = 0;
            if ($policy->RiderAddOnId) {
                $rider = $policy->rideraddon;
                if ($rider) {
                    $riderPremium = $rider->AdditionalPremium ?? 0;
                }
            }
            $balance = ($policy->PremiumAmount + $riderPremium) - $totalPaid;
            $balances[$policy->Id] = $balance;
            return $balance != 0;
        });
        $paymentModes = CodeDetail::where('CodeID', 'PaymentModes')->get();

        return view('bancassurance.premiums.create', [
            'policies' => $filteredPolicies,
            'paymentModes' => $paymentModes,
            'payment' => $payment,
            'balances' => $balances,
            'currencies' => $currencies,
        ]);
    }

    public function store(BancassurancePremiumPaymentsRequest $request)
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsCreate, BancassurancePremiumPayments::class);
        $validated = $request->validated();

        $PolicyID = BancassurancePolicy::findOrFail($validated['PolicyID']);
        $PaymentMode = CodeDetail::findOrFail($validated['PaymentMode']);
        $PaymentDate = new \DateTime($validated['PaymentDate']);
        $NextPaymentDate = new \DateTime($validated['NextPaymentDate']);
        $CurrencyId = Currency::findOrFail($validated['CurrencyId']);


        $customer = BancassurancePremiumPaymentsService::create(
            $PolicyID,
            $validated['CustomerID'],
            $validated['PaymentFrequency'],
            $PaymentDate,
            $NextPaymentDate,
            $validated['Amount'],
            $CurrencyId,
            $PaymentMode,
            $validated['ReferenceNumber'],
            $validated['Notes'] ?? '',
            Auth::user(),
        );
        return redirect()->route('bancassurance.premiums.index')->with('success', 'Premium payment recorded successfully.');
    }

    public function index()
    {
        $payments = BancassurancePremiumPayments::all();

        return view('bancassurance.premiums.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = BancassurancePremiumPayments::find($id);

        return view('bancassurance.premiums.show', compact('payment'));
    }

    public function printReceipt($id)
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsView, BancassurancePremiumPayments::class);
        $payment = BancassurancePremiumPayments::findOrFail($id);

        return view('bancassurance.premiums.receipt', compact('payment'));
    }


    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsDelete, BancassurancePremiumPayments::class);
        try {
            $payment = BancassurancePremiumPayments::findOrFail($id);
            $payment->delete();

            return redirect()->route('bancassurance.premiums.index')
                ->with('success', 'Premium Payments Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Premium Payments contacts: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Premium Payments Contacts. Please try again.'])
                ->withInput();
        }
    }
}
