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
        $invoices=[];
        $data=FinanceInvoiceEntry::with('currency:Id,Code')
                ->select('Id', 'InvoiceNumber','SupplierID','CurrencyID', 'InvoiceAmount')
                ->where('ApprovalStatus', 'posted')
                ->get();
        foreach ($data as $value) {
            //Get the invoice balance
            $invoice=FinanceInvoiceEntry::find($value->Id);
            $invoiceAmt=$invoice->InvoiceAmount;
            $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
            $balance=$invoiceAmt-$amtPaidOnInvoice;
            //Dont store invoies that are fully paid already
            if($balance<=0){
                continue;
            }
            $invoices[]=[
                'Id'=>$value->Id,
                'InvoiceNumber'=>$value->InvoiceNumber,
                'SupplierID'=>$value->SupplierID,
                'CurrencyID'=>$value->CurrencyID,
                'InvoiceAmount'=>$value->InvoiceAmount,
                'CurrencyCode'=>$value->currency->Code,
                'Balance'=>$balance
            ];
        }
        $paymentMethods=CodeDetail::where('CodeID', 'PaymentMethod')->get();
        $paymentTypes=CodeDetail::where('CodeID', 'PaymentType')->get();
        $paymentFrequencies=CodeDetail::where('CodeID', 'PaymentFrequency')->get();
        return view('finance.accountspayable.paymentvoucher.create', compact(
            'invoices',
            'VoucherNo',
            'paymentMethods',
            'paymentTypes',
            'paymentFrequencies',
        ));
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

        //Check if the Voucher amt exceeds the Invoice Balance and return back with an error
        $invoice=FinanceInvoiceEntry::find($validated['InvoiceNo']);
        $invoiceAmt=$invoice->InvoiceAmount;
        $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
        $balance=$invoiceAmt-$amtPaidOnInvoice;
        if($balance==0){
            return back()->with('error', 'This Invoice is already settled. Current balance is '.$balance);
        }
        if ($balance<$validated['TotAmnt']) {
            return back()->with('error', 'Invoice Balance is Exceeded. The current balance is '.$balance);
        }


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
                'TotalAmount'=> $validated['TotAmnt'],
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
            return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Created Successfully');

        }catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return back()->with('error', $th->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableUpdate, FinanceVoucher::class);

        $voucher = FinanceVoucher::findOrFail($id);

        $year = now()->year;
        $lastId = FinanceVoucher::max('Id') + 1;
        $VoucherNo = 'VCN-' . $year .'-'. str_pad($lastId, 6,'0', STR_PAD_LEFT);
        $invoices=[];
        $data=FinanceInvoiceEntry::with('currency:Id,Code')
                ->select('Id', 'InvoiceNumber','SupplierID','CurrencyID', 'InvoiceAmount')
                ->where('ApprovalStatus', 'posted')
                ->get();
        foreach ($data as $value) {
            //Get the invoice balance
            $invoice=FinanceInvoiceEntry::find($value->Id);
            $invoiceAmt=$invoice->InvoiceAmount;
            $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
            $balance=$invoiceAmt-$amtPaidOnInvoice;
            //Dont store invoies that are fully paid already
            if($balance<=0){
                continue;
            }
            $invoices[]=[
                'Id'=>$value->Id,
                'InvoiceNumber'=>$value->InvoiceNumber,
                'SupplierID'=>$value->SupplierID,
                'CurrencyID'=>$value->CurrencyID,
                'InvoiceAmount'=>$value->InvoiceAmount,
                'CurrencyCode'=>$value->currency->Code,
                'Balance'=>$balance
            ];
        }
        $paymentMethods=CodeDetail::where('CodeID', 'PaymentMethod')->get();
        $paymentTypes=CodeDetail::where('CodeID', 'PaymentType')->get();
        $paymentFrequencies=CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('finance.accountspayable.paymentvoucher.edit', compact(
            'voucher',
            'invoices',
            'VoucherNo',
            'paymentMethods',
            'paymentTypes',
            'paymentFrequencies'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableUpdate, FinanceVoucher::class);

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

        $invoice=FinanceInvoiceEntry::find($validated['InvoiceNo']);
        $invoiceAmt=$invoice->InvoiceAmount;
        $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
        $balance=$invoiceAmt-$amtPaidOnInvoice;
        if($balance==0){
            return back()->with('error', 'This Invoice is already settled. Current balance is '.$balance);
        }
        if ($balance<$validated['TotAmnt']) {
            return back()->with('error', 'Invoice Balance is Exceeded. The current balance is '.$balance);
        }

        DB::beginTransaction();

        $startdate = $request->input('StartDate');
        $frequency = $request->input('Frequency');

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

            $voucher = FinanceVoucher::findOrFail($id);
            $voucher->update([
                'VoucherNo'=> $validated['VoucherNo'],
                'InvoiceNo'=> $validated['InvoiceNo'],
                'TotalAmount'=> $validated['TotAmnt'],
                'PaymentMethod'=> $validated['PaymentMethod'],
                'PaymentType'=> $validated['PaymentType'],
                'StartDate'=> $request->input('StartDate'),
                'Frequency'=> $request->input('Frequency'),
                'Description'=>$validated['Description'],
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Voucher Successfully'. $voucher->id);

            DB::commit();
            return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Updated Successfully');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return back()->with('error', $th->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceVoucher::class);

        $voucher = FinanceVoucher::with('invoice.supplier')->findOrFail($id);
        $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $voucher->InvoiceNo)->where('ApprovalStatus','posted')->sum('TotalAmount');
        $statusClass = match($voucher->ApprovalStatus) {
            'posted' => 'bg-success',
            'rejected' => 'bg-danger',
            'draft' => 'bg-warning text-dark',
            default => 'bg-secondary'
        };

        return view('finance.accountspayable.paymentvoucher.show', compact('voucher', 'amtPaidOnInvoice','statusClass'));
    }

    public function approve(Request $request, $id)
    {
        $voucher = FinanceVoucher::findOrFail($id);
        $voucher->ApprovalStatus = 'posted';
        // Optionally log reason: $request->input('reason')
        $voucher->Reasons = $request->Reasons;
        $voucher->save();

        return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Approved.');
    }

    public function reject(Request $request, $id)
    {
        $voucher = FinanceVoucher::findOrFail($id);
        $voucher->Status = 'rejected';
        $voucher->ApprovalStatus = 'rejected';
        // Optionally log reason: $request->input('reason')
        $voucher->Reasons = $request->Reasons;
        $voucher->save();

        return redirect()->route('paymentvoucher.index')->with('error', 'Voucher Rejected.');
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableDelete, FinanceVoucher::class);

        $voucher = FinanceVoucher::findOrFail($id);
        if ($voucher->ApprovalStatus !== 'draft') {
            return redirect()->route('paymentvoucher.index')->with('error', 'Only draft vouchers can be deleted.');
        }
        $voucher->DeletedBy = Auth::id();
        $voucher->save();
        $voucher->delete();

        activity()
            ->performedOn($voucher)
            ->causedBy(Auth::user())
            ->withProperties(['action'=>'delete'])
            ->log('Deleted Voucher Successfully'. $voucher->id);

        return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Deleted Successfully');
    }
}
