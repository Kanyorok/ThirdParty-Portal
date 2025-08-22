<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Insurance\PremiumManagement\BancassurancePremiumPaymentsRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Insurance\BancassurancePolicy;
use App\Services\Insurance\PremiumManagement\BancassurancePremiumPaymentsService;
use App\Models\Core\CodeDetail;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassurancePremiumPayments;

class PremiumController extends Controller
{
    //
    public function create()
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsView, BancassurancePremiumPayments::class);
        $payment = BancassurancePremiumPayments::all();
        $policies = BancassurancePolicy::all();
        $paymentModes = CodeDetail::where('CodeID', 'PaymentModes')->get();

        return view('bancassurance.premiums.create', compact('policies', 'paymentModes', 'payment'));
    }

    public function store(BancassurancePremiumPaymentsRequest $request)
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsCreate, BancassurancePremiumPayments::class);
        $validated = $request->validated();

        $PolicyID = BancassurancePolicy::findOrFail($validated['PolicyID']);
        $PaymentMode = CodeDetail::findOrFail($validated['PaymentMode']);
        $PaymentDate = new \DateTime($validated['PaymentDate']);
        $NextPaymentDate = new \DateTime($validated['NextPaymentDate']);

        $customer = BancassurancePremiumPaymentsService::create(
            $PolicyID,
            $validated['CustomerID'],
            $validated['PaymentFrequency'],
            $PaymentDate,
            $NextPaymentDate,
            $validated['Amount'],
            $PaymentMode,
            $validated['ReferenceNumber'],
            $validated['Notes'],
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

    public function edit($Id)
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsView, BancassurancePremiumPayments::class);
        $payment = BancassurancePremiumPayments::findOrFail($Id);
        $policies = BancassurancePolicy::all();
        $paymentModes = CodeDetail::where('CodeID', 'PaymentModes', 'payment')->get();

        return view('bancassurance.premiums.edit', compact('policies', 'paymentModes', 'payment'));
    }

    public function update(BancassurancePremiumPaymentsRequest $request, $id)
    {
        $this->authorize(PermissionEnum::BancassurancePremiumPaymentsUpdate, BancassurancePremiumPayments::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $validated['PaymentDate'] = \Carbon\Carbon::parse($validated['PaymentDate'])->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->withErrors(['PaymentDate' => 'Invalid date format.'])->withInput();
        }

        try {
            $payment = BancassurancePremiumPayments::findOrFail($id);

            $payment->update([
                'PolicyID' => $validated['PolicyID'],
                'CustomerID' => $validated['CustomerID'],
                'PaymentFrequency' => $validated['PaymentFrequency'],
                'PaymentDate' => $validated['PaymentDate'],
                'NextPaymentDate' => $validated['NextPaymentDate'],
                'Amount' => $validated['Amount'],
                'PaymentMode' => $validated['PaymentMode'],
                'ReferenceNumber' => $validated['ReferenceNumber'],
                'Notes' => $validated['Notes'],
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($payment)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Premium Payments');

            return redirect()->route('bancassurance.premiums.index')->with('success', 'Premium Payments updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Premium Payments:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update Premium Payments'])->withInput();
        }
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


