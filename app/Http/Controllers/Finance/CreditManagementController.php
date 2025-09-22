<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Property\TenantClearanceEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyTenantClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditManagementController extends Controller
{
    public function index()
    {
        $credits = FinanceCreditManagement::select('Id', 'CustomerID', 'CreditLimit', 'Status', 'EffectiveFrom', 'ExpiryDate')
            ->with(['customer:Id,TenantName,IDRegistrationNo'])
            ->get();
        return view('finance.accountsreceivable.creditmanagement.index', compact('credits'));
    }

    public function create(){

        $paymentTerms = CodeDetail::select('Value', 'Description')
            ->where('CodeID', 'PaymentTerm')
            ->get();
        $customers = PropertyNewTenant::select('Id', 'TenantName', 'IDRegistrationNo', 'EmailAddress')
            ->where('IsActive', 1)
            ->get();
        return view('finance.accountsreceivable.creditmanagement.create', compact('paymentTerms', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CustomerID' => 'required|exists:t_TenantMaintenance,Id',
            'CreditLimit' => 'required|numeric|min:0',
            'PaymentTerms' => 'required|exists:t_CodeDetails,Value',
            'EffectiveFrom' => 'required|date',
            'ExpiryDate' => 'required|date|after_or_equal:EffectiveFrom',
            'Colleteral' => 'required|string|max:255',
            'Remarks' => 'required|string|max:1000',
        ]);

        try {

            DB::beginTransaction();

            $creditManagement = FinanceCreditManagement::create([
                'CustomerID' => $validated['CustomerID'],
                'CreditLimit' => $validated['CreditLimit'],
                'PaymentTerms' => $validated['PaymentTerms'],
                'EffectiveFrom' => $validated['EffectiveFrom'],
                'ExpiryDate' => $validated['ExpiryDate'],
                'Colleteral' => $validated['Colleteral'],
                'Remarks' => $validated['Remarks'],
                'Status' => 'Active',
                'ApprovalStatus' => 'Pending',
                'ApprovalReason' => null,
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn(new FinanceCreditManagement())
                ->withProperties(['action' => 'create'])
                ->causedBy(Auth::user())
                ->log('Created Credit Management for Customer ID: ' . $validated['CustomerID']);

            DB::commit();

            return redirect()->route('creditmanagement.index')->with('success', 'Credit Management created successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();

            return $th->getMessage();

            activity()
                ->performedOn(new FinanceCreditManagement())
                ->causedBy(Auth::user())
                ->log('Failed to create Credit Management: ' . $th->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to create credit management: ' . $th->getMessage()]);
        }

        // Logic to store credit management data

        return redirect()->route('creditmanagement.index')->with('success', 'Credit Management created successfully.');
    }

    public function show($id)
    {
        return view('finance.accountsreceivable.creditmanagement.show');
    }
}
