<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceVoucher;
use App\Models\ThirdParies\Supplier;
use FacebookAds\Object\FinanceObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceVoucher::class);


        $vouchers = FinanceVoucher::with('invoice:Id,InvoiceNumber')
            ->select('Id', 'VoucherNo', 'InvoiceNo', 'TotalAmount', 'PaymentMethod',
                            'ApprovalStatus','PaymentType', 'Description')
            ->get();
        return view('finance.accountspayable.paymentvoucher.index', compact('vouchers'));
    }

    public function create(){

        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceVoucher::class);

        $year = now()->year;
        $lastId = FinanceVoucher::max('Id') + 1;
        $VoucherNo = 'VCN-' . $year .'-'. str_pad($lastId, 6,'0', STR_PAD_LEFT);
        $invoices = FinanceInvoiceEntry::with('currency:Id,Code')
                            ->select('Id', 'InvoiceNumber','SupplierID','CurrencyID', 'InvoiceAmount')
            ->get();
        $paymentMethods=CodeDetail::where('CodeID', 'PaymentMethod')->get();
        $paymentTypes=CodeDetail::where('CodeID', 'PaymentType')->get();
        return view('finance.accountspayable.paymentvoucher.create', compact('invoices', 'VoucherNo', 'paymentMethods', 'paymentTypes'));
    }

    public function store(Request $request){
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceVoucher::class);

        $validated = $request->validate([
            'VoucherNo'=> 'required|string',
            'InvoiceNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
            'TotAmnt'=>'required|numeric|min:0.00',
            'PaymentMethod'=>'required|string',
            'PaymentType'=> 'required|string',
            'StartDate'=> 'nullable|date',
            'Frequency'=> 'nullable|string',
            'Description'=>'required|string',
        ]);

        DB::beginTransaction();

        // $partial = $request->input('PartialAmnt');
        $startdate = $request->input('StartDate');
        $frequency = $request->input('Frequency');
        // $api = $request->input('AmntPerInst');

        try {

            if($validated['PaymentType'] == 'Full'){
                $startdate = null;
                $frequency = null;
            }elseif($validated['PaymentType'] == 'Partial'){
                $startdate = null;
                $frequency = null;
            }else{

                $startdate = $request->input('StartDate');
                $frequency = $request->input('Frequency');
            }

            $voucher = FinanceVoucher::create([
                'VoucherNo'=> $validated['VoucherNo'],
                'InvoiceNo'=> $validated['InvoiceNo'],
                'TotAmnt'=> $validated['TotAmnt'],
                'PaymentMethod'=> $validated['PaymentMethod'],
                'PaymentType'=> $validated['PaymentType'],
                'StartDate'=> $startdate,
                'Frequency'=> $frequency,
                'Description'=>$validated['Description'],
                'CreatedBy'  =>Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'create'])
                ->log('Created Voucher Successfully'. $voucher->id);

            DB::commit();
            return redirect()->route('paymentvoucher.index')->with('Success', 'Invoice Created Successfully');

        }catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return back()->with('error', $th->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceVoucher::class);

        $voucher = FinanceVoucher::findOrFail($id);

        return view('finance.accountspayable.paymentvoucher.show', compact('voucher'));
    }

    public function approve(Request $request, $id)
    {
        $voucher = FinanceVoucher::findOrFail($id);
        $voucher->Status = 'Approved';
        // Optionally log reason: $request->input('reason')
        $voucher->Reasons = $request->Reasons;
        $voucher->save();

        return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Approved.');
    }

    public function reject(Request $request, $id)
    {
        $voucher = FinanceVoucher::findOrFail($id);
        $voucher->Status = 'Rejected';
        // Optionally log reason: $request->input('reason')
        $voucher->Reasons = $request->Reasons;
        $voucher->save();

        return redirect()->route('paymentvoucher.index')->with('error', 'Voucher Rejected.');
    }
}
