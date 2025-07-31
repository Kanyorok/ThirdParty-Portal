<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceVoucher;
use App\Models\ThirdParies\Supplier;
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
            ->select('Id', 'VoucherNo', 'InvoiceNo', 'TotAmnt', 'PaymentMethod', 'Status', 'PaymentType', 'Description')
            ->get();
        return view('finance.accountspayable.paymentvoucher.index', compact('vouchers'));
    }

    public function create(){

        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceVoucher::class);

        $invoices = FinanceInvoiceEntry::select('Id', 'InvoiceNumber','SupplierID','CurrencyID', 'InvoiceAmount')
            ->get();
        return view('finance.accountspayable.paymentvoucher.create', compact('invoices'));
    }

    public function store(Request $request){
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceVoucher::class);

        $validated = $request->validate([
            'VoucherNo'=> 'required|string',
            'InvoiceNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
            'TotAmnt'=>'required|numeric|min:0.00',
            'PaymentMethod'=>'required|string',
            'PaymentType'=> 'required|string',
            'PartialAmnt'=>'nullable|numeric',
            'StartDate'=> 'nullable|date',
            'Frequency'=> 'nullable|string',
            'AmntPerInst'=> 'nullable|numeric',
            'Description'=>'required|string',
        ]);

        DB::beginTransaction();

        $partial = $request->input('PartialAmnt');
        $startdate = $request->input('StartDate');
        $frequency = $request->input('Frequency');
        $api = $request->input('AmntPerInst');

        try {

            if($validated['PaymentType'] == 'Full'){
                $startdate = null;
                $frequency = null;
                $api = null;
                $partial = null;
            }elseif($validated['PaymentType'] == 'Partial'){
                $startdate = null;
                $frequency = null;
                $api = null;
                $partial = $request->input('PartialAmnt');
            }else{

                $startdate = $request->input('StartDate');
                $frequency = $request->input('Frequency');
                $api = $request->input('AmntPerInst');
                $partial = null;
            }

            $voucher = FinanceVoucher::create([
                'VoucherNo'=> $validated['VoucherNo'],
                'InvoiceNo'=> $validated['InvoiceNo'],
                'TotAmnt'=> $validated['TotAmnt'],
                'PaymentMethod'=> $validated['PaymentMethod'],
                'PaymentType'=> $validated['PaymentType'],
                'PartialAmnt'=> $partial,
                'StartDate'=> $startdate,
                'Frequency'=> $frequency,
                'AmntPerInst'=> $api,
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
}
