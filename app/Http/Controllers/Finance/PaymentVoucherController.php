<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentVoucherController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::PaymentVoucherView, FinanceVoucher::class);

        $query = FinanceVoucher::with('invoice:Id,InvoiceNumber')
            ->select(
                'Id',
                'VoucherNo',
                'InvoiceNo',
                'TotalAmount',
                'PaymentMethod',
                'ApprovalStatus',
                'PaymentType',
                'Description'
            );

        if ($request->filled('voucher_no')) {
            $query->where('VoucherNo', 'like', '%' . $request->voucher_no . '%');
        }
        if ($request->filled('invoice_number')) {
            $invNum = $request->invoice_number;
            $query->whereHas('invoice', function ($q) use ($invNum) {
                $q->where('InvoiceNumber', 'like', '%' . $invNum . '%');
            });
        }
        if ($request->filled('payment_method')) {
            $query->where('PaymentMethod', $request->payment_method);
        }
        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('ApprovalStatus', $request->approval_status);
        }
        if ($request->filled('payment_type')) {
            $query->where('PaymentType', $request->payment_type);
        }
        if ($request->filled('amount_min')) {
            $query->where('TotalAmount', '>=', (float)$request->amount_min);
        }

        $sortField = $request->sort_by ?? 'Id';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int)($request->per_page ?? 10);
        $vouchers = $query->paginate($perPage)->withQueryString();

        return view('finance.accountspayable.paymentvoucher.index', compact('vouchers'));
    }

    public function create()
    {

        $this->authorize(PermissionEnum::PaymentVoucherCreate, FinanceVoucher::class);

        $year = now()->year;
        $lastId = FinanceVoucher::max('Id') + 1;
        $VoucherNo = 'VCN-' . $year . '-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
        $invoices = [];
        $data = FinanceInvoiceEntry::with('currency:Id,Code')
                ->select('Id', 'InvoiceNumber', 'SupplierID', 'CurrencyID', 'InvoiceAmount')
                ->where('ApprovalStatus', 'posted')
                ->get();
        foreach ($data as $value) {
            //Get the invoice balance
            $invoice = FinanceInvoiceEntry::find($value->Id);
            $invoiceAmt = $invoice->InvoiceAmount;
            $amtPaidOnInvoice = FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus', 'posted')->sum('TotalAmount');
            $balance = $invoiceAmt - $amtPaidOnInvoice;
            //Dont store invoies that are fully paid already
            if ($balance <= 0) {
                continue;
            }
            $invoices[] = [
                'Id' => $value->Id,
                'InvoiceNumber' => $value->InvoiceNumber,
                // ThirdPartyID is stored in SupplierID field for AP module
                'ThirdPartyID' => $value->SupplierID,
                'CurrencyID' => $value->CurrencyID,
                'InvoiceAmount' => $value->InvoiceAmount,
                'CurrencyCode' => $value->currency->Code,
                'Balance' => $balance,
            ];
        }
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')->get();
        $paymentTypes = CodeDetail::where('CodeID', 'PaymentType')->get();
        $paymentFrequencies = CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('finance.accountspayable.paymentvoucher.create', compact(
            'invoices',
            'VoucherNo',
            'paymentMethods',
            'paymentTypes',
            'paymentFrequencies',
        ));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::PaymentVoucherCreate, FinanceVoucher::class);

        $validated = $request->validate([
            //'VoucherNo'=> 'required|string',
            'InvoiceNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
            'TotAmnt' => 'required|numeric|min:0.00',
            'PaymentMethod' => 'required|string',
            'PaymentType' => 'required|string',
            'StartDate' => 'nullable|date',
            'Frequency' => 'nullable|string',
            'Description' => 'required|string',
        ]);

        //Check if the Voucher amt exceeds the Invoice Balance and return back with an error
        $invoice = FinanceInvoiceEntry::find($validated['InvoiceNo']);
        $invoiceAmt = $invoice->InvoiceAmount;
        $amtPaidOnInvoice = FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus', 'posted')->sum('TotalAmount');
        $balance = $invoiceAmt - $amtPaidOnInvoice;
        if ($balance == 0) {
            return back()->with('error', 'This Invoice is already settled. Current balance is ' . $balance);
        }
        if ($balance < $validated['TotAmnt']) {
            return back()->with('error', 'Invoice Balance is Exceeded. The current balance is ' . $balance);
        }


        DB::beginTransaction();

        // $partial = $request->input('PartialAmnt');
        $startdate = $request->input('StartDate');
        $frequency = $request->input('Frequency');
        // $api = $request->input('AmntPerInst');

        try {
            if ($validated['PaymentType'] == 'Full') {
                $startdate = null;
                $frequency = null;
            } elseif ($validated['PaymentType'] == 'Partial') {
                $startdate = null;
                $frequency = null;
            } else {
                $startdate = $request->input('StartDate');
                $frequency = $request->input('Frequency');
            }

            $voucher = FinanceVoucher::create([
//                'VoucherNo'=> $validated['VoucherNo'],
                'InvoiceNo' => $validated['InvoiceNo'],
                'TotalAmount' => $validated['TotAmnt'],
                'PaymentMethod' => $validated['PaymentMethod'],
                'PaymentType' => $validated['PaymentType'],
                'StartDate' => $startdate,
                'Frequency' => $frequency,
                'Description' => $validated['Description'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Voucher Successfully' . $voucher->id);

            DB::commit();

            return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Created Successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());

            //            return back()->with('error', $th->getMessage());
            return back()->with('error', 'Ooops! An error occurred, Please try again later.');
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::PaymentVoucherUpdate, FinanceVoucher::class);

        $voucher = FinanceVoucher::findOrFail($id);

        $year = now()->year;
        $lastId = FinanceVoucher::max('Id') + 1;
        $VoucherNo = 'VCN-' . $year . '-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
        $invoices = [];
        $data = FinanceInvoiceEntry::with('currency:Id,Code')
                ->select('Id', 'InvoiceNumber', 'SupplierID', 'CurrencyID', 'InvoiceAmount')
                ->where('ApprovalStatus', 'posted')
                ->get();
        foreach ($data as $value) {
            //Get the invoice balance
            $invoice = FinanceInvoiceEntry::find($value->Id);
            $invoiceAmt = $invoice->InvoiceAmount;
            $amtPaidOnInvoice = FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus', 'posted')->sum('TotalAmount');
            $balance = $invoiceAmt - $amtPaidOnInvoice;
            //Dont store invoies that are fully paid already
            if ($balance <= 0) {
                continue;
            }
            $invoices[] = [
                'Id' => $value->Id,
                'InvoiceNumber' => $value->InvoiceNumber,
                'ThirdPartyID' => $value->SupplierID,
                'CurrencyID' => $value->CurrencyID,
                'InvoiceAmount' => $value->InvoiceAmount,
                'CurrencyCode' => $value->currency->Code,
                'Balance' => $balance,
            ];
        }
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')->get();
        $paymentTypes = CodeDetail::where('CodeID', 'PaymentType')->get();
        $paymentFrequencies = CodeDetail::where('CodeID', 'PaymentFrequency')->get();


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
        $this->authorize(PermissionEnum::PaymentVoucherUpdate, FinanceVoucher::class);

        $validated = $request->validate([
            'VoucherNo' => 'required|string',
            'InvoiceNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
            'TotAmnt' => 'required|numeric|min:0.00',
            'PaymentMethod' => 'required|string',
            'PaymentType' => 'required|string',
            'StartDate' => 'nullable|date',
            'Frequency' => 'nullable|string',
            'Description' => 'required|string',
        ]);

        $invoice = FinanceInvoiceEntry::find($validated['InvoiceNo']);
        $invoiceAmt = $invoice->InvoiceAmount;
        $amtPaidOnInvoice = FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus', 'posted')->sum('TotalAmount');
        $balance = $invoiceAmt - $amtPaidOnInvoice;
        if ($balance == 0) {
            return back()->with('error', 'This Invoice is already settled. Current balance is ' . $balance);
        }
        if ($balance < $validated['TotAmnt']) {
            return back()->with('error', 'Invoice Balance is Exceeded. The current balance is ' . $balance);
        }

        DB::beginTransaction();

        $startdate = $request->input('StartDate');
        $frequency = $request->input('Frequency');

        try {
            if ($validated['PaymentType'] == 'Full') {
                $startdate = null;
                $frequency = null;
            } elseif ($validated['PaymentType'] == 'Partial') {
                $startdate = null;
                $frequency = null;
            } else {
                $startdate = $request->input('StartDate');
                $frequency = $request->input('Frequency');
            }

            $voucher = FinanceVoucher::findOrFail($id);
            $voucher->update([
                'VoucherNo' => $validated['VoucherNo'],
                'InvoiceNo' => $validated['InvoiceNo'],
                'TotalAmount' => $validated['TotAmnt'],
                'PaymentMethod' => $validated['PaymentMethod'],
                'PaymentType' => $validated['PaymentType'],
                'StartDate' => $request->input('StartDate'),
                'Frequency' => $request->input('Frequency'),
                'Description' => $validated['Description'],
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Voucher Successfully' . $voucher->id);

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
        $this->authorize(PermissionEnum::PaymentVoucherView, FinanceVoucher::class);

        $voucher = FinanceVoucher::with([
            'invoice.thirdParty:Id,TradingName,ThirdPartyName,Email,Phone,PhysicalAddress',
            'invoice.currency:Id,Code',
        ])->findOrFail($id);
        $amtPaidOnInvoice = FinanceVoucher::where('InvoiceNo', $voucher->InvoiceNo)->where('ApprovalStatus', 'posted')->sum('TotalAmount');
        $statusClass = match ($voucher->ApprovalStatus) {
            'posted' => 'bg-success',
            'rejected' => 'bg-danger',
            'draft' => 'bg-warning text-dark',
            default => 'bg-secondary'
        };

        // Convert Amount into words using inbuilt php function
        //$amountInWords = number_format($voucher->TotalAmount, 2, '.', ',');

        $amountInWords = $this->numberToWords($voucher->TotalAmount);

        return view('finance.accountspayable.paymentvoucher.show', compact('voucher', 'amtPaidOnInvoice', 'statusClass', 'amountInWords'));
    }

    private function numberToWords($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion',
        ];

        if (! is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'numberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );

            return false;
        }

        if ($number < 0) {
            return $negative . $this->numberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];

                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }

                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->numberToWords($remainder);
                }

                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->numberToWords($remainder);
                }

                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    public function approve(Request $request, $id)
    {
        // $this->authorize(PermissionEnum::PaymentVoucherApprove, FinanceVoucher::class);
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
        $this->authorize(PermissionEnum::PaymentVoucherDelete, FinanceVoucher::class);

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
            ->withProperties(['action' => 'delete'])
            ->log('Deleted Voucher Successfully' . $voucher->id);

        return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Deleted Successfully');
    }
}
