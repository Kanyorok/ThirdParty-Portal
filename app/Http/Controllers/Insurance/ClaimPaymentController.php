<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassuranceClaimPaymentRequest;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceClaimPayment;
use App\Services\Insurance\BancassuranceClaimPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClaimPaymentController extends Controller
{
    //
public function create()
{
    $unpaidClaims = BancassuranceClaim::all();
    $payments = CodeDetail::where('CodeID','PaymentMethod')->get();

    return view('bancassurance.claims.payments.create', compact('unpaidClaims','payments'));
}

public function index()
{
    $payments = BancassuranceClaimPayment::with('payment','claim')->get();

        return view('bancassurance.claims.payments.index', compact('payments'));
    }

public function store(BancassuranceClaimPaymentRequest $request)
{
    $validated = $request->validated();

    $ClaimId = BancassuranceClaim::findOrFail($validated['ClaimId']);
    $PaymentMethod = CodeDetail::findOrFail($validated['PaymentMethod']);

    $payment = BancassuranceClaimPaymentService::create(
        $ClaimId,
        Carbon::parse($validated['PaymentDate']),
        $validated['PaymentAmount'],
        $validated['PaymentReference'],
        $validated['Note'],
        $validated['PaidBy'],
        $PaymentMethod,
        $request->user(),
    );

    // Optionally update status of claim to "Paid"
    DB::table('t_BancassuranceClaims')
        ->where('Id', $request->ClaimId)
        ->update([
            'Status' => CodeDetail::where('CodeID', 'ClaimStatus')->where('Value', 'P')->value('ID'),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('bancassurance.claims.payments.index')
            ->with('success', 'Payment processed successfully.');
    }

}
