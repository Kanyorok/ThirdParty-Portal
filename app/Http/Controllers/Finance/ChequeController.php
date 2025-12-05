<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Finance\BankAccount;
use App\Models\Finance\Cashbook;
use App\Models\Finance\CashbookLine;
use App\Models\Finance\Cheque;
use App\Models\Finance\ChequeBook;
use App\Models\Finance\FinanceGLMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChequeController extends Controller
{
    // Module mapping for cheque actions (configure in your t_Modules)
    private const CHEQUE_MODULE_ID = 1102800;
    private const CASHBOOK_MODULE_ID = 1102400; // fallback for mappings that use Cashbook

    public function index(Request $request)
    {
        $dir = strtoupper($request->query('dir', 'ISSUED')); // ISSUED | RECEIVED
        $status = $request->query('status');

        $q = Cheque::with(['bankAccount.bank', 'chequeBook', 'currency'])
            ->where('Direction', $dir)
            ->orderByDesc('ChequeID');

        if ($status) $q->where('Status', $status);

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function($query) use ($s) {
                $query->where('ChequeNumber', 'like', "%$s%")
                      ->orWhere('PartyName', 'like', "%$s%")
                      ->orWhere('Amount', 'like', "%$s%")
                      ->orWhere('ChequeID', $s);
            });
        }

        $rows = $q->paginate(25);
        
        // Fetch CodeDetails
        $chequeLeafStatuses = CodeDetail::where('CodeID', 'ChequeLeafStatus')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();
            
        return view('finance.cheques.index', compact('rows', 'dir', 'status', 'chequeLeafStatuses'));
    }

    // ---------- Create ----------
    public function createIssued()
    {
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        $books = ChequeBook::where('IsActive', 1)->orderByDesc('ChequeBookID')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'DecimalDigits']);
        
        // Fetch CodeDetails
        $chequePartyTypes = CodeDetail::where('CodeID', 'ChequePartyType')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();

        return view('finance.cheques.issued.create', compact('bankAccounts', 'books', 'currencies', 'chequePartyTypes'));
    }

    public function createReceived()
    {
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get(); // for deposit later
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'DecimalDigits']);
        
        // Fetch CodeDetails
        $chequePartyTypes = CodeDetail::where('CodeID', 'ChequePartyType')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();
            
        return view('finance.cheques.received.create', compact('bankAccounts', 'currencies', 'chequePartyTypes'));
    }

    // ---------- Store ----------
    public function storeIssued(Request $request)
    {
        $request->validate([
            'ChequeBookID' => 'required|integer|exists:t_ChequeBooks,ChequeBookID',
            'ChequeNumber' => 'required|string|max:50',
            'ChequeDate' => 'nullable|date',
            'DueDate' => 'nullable|date',
            'IsPostDated' => 'nullable|boolean',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'Amount' => 'required|numeric|min:0.01',
            'PartyType' => 'nullable|string|max:20',
            'PartyID' => 'nullable|integer',
            'PartyName' => 'nullable|string|max:200',
            'Reference' => 'nullable|string|max:100',
            'Narration' => 'nullable|string|max:300',
        ]);

        return DB::transaction(function () use ($request) {
            $book = ChequeBook::lockForUpdate()->findOrFail($request->ChequeBookID);

            // If ChequeNumber not provided, pull the first Unused leaf >= NextLeafNumber
            $chequeNumber = trim($request->ChequeNumber ?? '');
            if ($chequeNumber === '') {
                $leaf = \App\Models\Finance\ChequeLeaf::where('ChequeBookID', $book->ChequeBookID)
                    ->where('Status', 'Unused')
                    ->where('LeafNumber', '>=', $book->NextLeafNumber)
                    ->orderBy('LeafNumber')
                    ->first();
                if (!$leaf) {
                    return back()->withInput()->withErrors(['ChequeNumber' => 'No unused leaves available in this book.']);
                }
                $chequeNumber = $leaf->ChequeNumber;
            } else {
                $leaf = \App\Models\Finance\ChequeLeaf::where('ChequeBookID', $book->ChequeBookID)
                    ->where('ChequeNumber', $chequeNumber)->lockForUpdate()->first();
                if (!$leaf) {
                    return back()->withInput()->withErrors(['ChequeNumber' => 'Cheque number not found in this book.']);
                }
                if ($leaf->Status !== 'Unused') {
                    return back()->withInput()->withErrors(['ChequeNumber' => 'This leaf is already ' . $leaf->Status . '.']);
                }


                return DB::transaction(function () use ($request) {
                    $book = ChequeBook::lockForUpdate()->findOrFail($request->ChequeBookID);

                    // If ChequeNumber not provided, pull the first Unused leaf >= NextLeafNumber
                    $chequeNumber = trim($request->ChequeNumber ?? '');
                    if ($chequeNumber === '') {
                        $leaf = \App\Models\Finance\ChequeLeaf::where('ChequeBookID', $book->ChequeBookID)
                            ->where('Status', 'Unused')
                            ->where('LeafNumber', '>=', $book->NextLeafNumber)
                            ->orderBy('LeafNumber')
                            ->first();
                        if (!$leaf) {
                            return back()->withInput()->withErrors(['ChequeNumber' => 'No unused leaves available in this book.']);
                        }
                        $chequeNumber = $leaf->ChequeNumber;
                    } else {
                        $leaf = \App\Models\Finance\ChequeLeaf::where('ChequeBookID', $book->ChequeBookID)
                            ->where('ChequeNumber', $chequeNumber)->lockForUpdate()->first();
                        if (!$leaf) {
                            return back()->withInput()->withErrors(['ChequeNumber' => 'Cheque number not found in this book.']);
                        }
                        if ($leaf->Status !== 'Unused') {
                            return back()->withInput()->withErrors(['ChequeNumber' => 'This leaf is already ' . $leaf->Status . '.']);
                        }
                    }

                    // Create cheque record (as before)
                    $row = Cheque::create([
                        'Direction' => 'ISSUED',
                        'BankAccountID' => $book->BankAccountID,
                        'ChequeBookID' => $book->ChequeBookID,
                        'ChequeNumber' => $chequeNumber,
                        'ChequeDate' => $request->ChequeDate,
                        'DueDate' => $request->DueDate,
                        'IsPostDated' => $request->boolean('IsPostDated'),
                        'CurrencyID' => $request->CurrencyID,
                        'Amount' => $request->Amount,
                        'PartyType' => $request->PartyType,
                        'PartyID' => $request->PartyID,
                        'PartyName' => $request->PartyName,
                        'Status' => 'Issued',
                        'Reference' => $request->Reference,
                        'Narration' => $request->Narration,
                    ]);

                    // Consume the leaf
                    $leaf->Status = 'Issued';
                    $leaf->ChequeID = $row->ChequeID;
                    $leaf->UsedOn = now()->toDateString();
                    $leaf->save();

                    // Advance NextLeafNumber if needed
                    $leafNum = (int)$leaf->LeafNumber;
                    if ($leafNum === (int)$book->NextLeafNumber) {
                        $book->NextLeafNumber = min((int)$book->EndNumber, $leafNum + 1);
                    }
                    $book->LeavesIssued = min((int)$book->LeavesTotal, (int)$book->LeavesIssued + 1);
                    $book->save();

                    return redirect()->route('finance.cheques.show', $row->ChequeID)
                        ->with('success', 'Issued cheque recorded; leaf consumed.');
                });
            }

            // Create cheque record (as before)
            $row = Cheque::create([
                'Direction' => 'ISSUED',
                'BankAccountID' => $book->BankAccountID,
                'ChequeBookID' => $book->ChequeBookID,
                'ChequeNumber' => $chequeNumber,
                'ChequeDate' => $request->ChequeDate,
                'DueDate' => $request->DueDate,
                'IsPostDated' => $request->boolean('IsPostDated'),
                'CurrencyID' => $request->CurrencyID,
                'Amount' => $request->Amount,
                'PartyType' => $request->PartyType,
                'PartyID' => $request->PartyID,
                'PartyName' => $request->PartyName,
                'Status' => 'Issued',
                'Reference' => $request->Reference,
                'Narration' => $request->Narration,
            ]);

            // Consume the leaf
            $leaf->Status = 'Issued';
            $leaf->ChequeID = $row->ChequeID;
            $leaf->UsedOn = now()->toDateString();
            $leaf->save();

            // Advance NextLeafNumber if needed
            $leafNum = (int)$leaf->LeafNumber;
            if ($leafNum === (int)$book->NextLeafNumber) {
                $book->NextLeafNumber = min((int)$book->EndNumber, $leafNum + 1);
            }
            $book->LeavesIssued = min((int)$book->LeavesTotal, (int)$book->LeavesIssued + 1);
            $book->save();

            return redirect()->route('finance.cheques.show', $row->ChequeID)
                ->with('success', 'Issued cheque recorded; leaf consumed.');
        });
        return DB::transaction(function () use ($request) {
            $book = ChequeBook::lockForUpdate()->findOrFail($request->ChequeBookID);

            // validate number within book range
            $num = (int)preg_replace('/\D/', '', $request->ChequeNumber);
            if ($num < $book->StartNumber || $num > $book->EndNumber) {
                return back()->withInput()->withErrors(['ChequeNumber' => 'Cheque number is out of book range.']);


            }

            $row = Cheque::create([
                'Direction' => 'ISSUED',
                'BankAccountID' => $book->BankAccountID, // source bank account from book
                'ChequeBookID' => $book->ChequeBookID,
                'ChequeNumber' => $request->ChequeNumber,
                'ChequeDate' => $request->ChequeDate,
                'DueDate' => $request->DueDate,
                'IsPostDated' => $request->boolean('IsPostDated'),
                'CurrencyID' => $request->CurrencyID,
                'Amount' => $request->Amount,
                'PartyType' => $request->PartyType,
                'PartyID' => $request->PartyID,
                'PartyName' => $request->PartyName,
                'Status' => 'Issued',
                'Reference' => $request->Reference,
                'Narration' => $request->Narration,
            ]);

            // update book pointers if this is the next leaf
            if ($num === (int)$book->NextLeafNumber) {
                $book->NextLeafNumber = min($book->EndNumber, $book->NextLeafNumber + 1);
            }
            $book->LeavesIssued = min($book->LeavesTotal, $book->LeavesIssued + 1);
            $book->save();

            return redirect()->route('finance.cheques.show', $row->ChequeID)
                ->with('success', 'Issued cheque recorded.');
        });
    }

    public function storeReceived(Request $request)
    {
        $request->validate([
            'ChequeNumber' => 'required|string|max:50',
            'ChequeDate' => 'nullable|date',
            'DueDate' => 'nullable|date',
            'IsPostDated' => 'nullable|boolean',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'Amount' => 'required|numeric|min:0.01',
            'PartyType' => 'nullable|string|max:20',
            'PartyID' => 'nullable|integer',
            'PartyName' => 'nullable|string|max:200',
            'Reference' => 'nullable|string|max:100',
            'Narration' => 'nullable|string|max:300',
        ]);

        $row = Cheque::create([
            'Direction' => 'RECEIVED',
            'BankAccountID' => null, // choose when depositing
            'ChequeBookID' => null,
            'ChequeNumber' => $request->ChequeNumber,
            'ChequeDate' => $request->ChequeDate,
            'DueDate' => $request->DueDate,
            'IsPostDated' => $request->boolean('IsPostDated'),
            'CurrencyID' => $request->CurrencyID,
            'Amount' => $request->Amount,
            'PartyType' => $request->PartyType,
            'PartyID' => $request->PartyID,
            'PartyName' => $request->PartyName,
            'Status' => 'OnHand', // not yet banked
            'ReceivedDate' => now()->toDateString(),
            'Reference' => $request->Reference,
            'Narration' => $request->Narration,
        ]);

        return redirect()->route('finance.cheques.show', $row->ChequeID)
            ->with('success', 'Received cheque recorded (On Hand).');
    }

    // ---------- Show ----------
    public function show($id)
    {
        $row = Cheque::with(['bankAccount.bank', 'chequeBook', 'currency'])->findOrFail($id);
        $amountInWords = $this->numberToWords($row->Amount);
        
        // Fetch CodeDetails
        $chequeLeafStatuses = CodeDetail::where('CodeID', 'ChequeLeafStatus')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();
            
        return view('finance.cheques.show', compact('row', 'amountInWords', 'chequeLeafStatuses'));
    }

    private function numberToWords($number) {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'forty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
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
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
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
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    // ---------- Actions ----------
    // Deposit a RECEIVED cheque -> creates Cashbook RECEIPT (counter = Cheques-On-Hand mapping)
    public function deposit(Request $request, $id)
    {
        $row = Cheque::findOrFail($id);
        if ($row->Direction !== 'RECEIVED' || !in_array($row->Status, ['OnHand', 'Deposited'])) {
            return back()->with('error', 'Only OnHand received cheques can be deposited.');
        }

        $request->validate([
            'BankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'DocDate' => 'required|date',
        ]);

        // Mapping: expect CreditGLAccountID to be Cheques-On-Hand (counter CR)
        $map = FinanceGLMapping::where('ModuleID', self::CHEQUE_MODULE_ID)
            ->where('IsActive', 1)
            ->whereHas('transactions', fn($q) => $q->where('Code', 'CHQ_RECEIVED_DEPOSIT'))
            ->first();

        if (!$map) {
            // fallback to cashbook module mapping
            $map = FinanceGLMapping::where('ModuleID', self::CASHBOOK_MODULE_ID)
                ->where('IsActive', 1)
                ->whereHas('transactions', fn($q) => $q->where('Code', 'CHQ_RECEIVED_DEPOSIT'))
                ->first();
        }
        if (!$map || !$map->CreditGLAccountID) {
            return back()->with('error', 'Mapping for CHQ_RECEIVED_DEPOSIT not configured.');
        }

        return DB::transaction(function () use ($request, $row, $map) {
            // cashbook receipt
            $cb = new Cashbook([
                'EntryType' => 'RECEIPT',
                'BankAccountID' => $request->BankAccountID,
                'DocDate' => $request->DocDate,
                'CurrencyID' => $row->CurrencyID,
                'ExchangeRate' => 1,
                'Amount' => $row->Amount,
                'AmountBase' => $row->Amount,
                'Reference' => 'CHQ-DEP-' . $row->ChequeID,
                'Narration' => 'Deposit cheque ' . $row->ChequeNumber,
                'Status' => 'Posted',
                'SourceModule' => 'CHEQUE',
                'SourceID' => $row->ChequeID,
                'IsSystemGenerated' => 1,
            ]);
            $cb->save();

            CashbookLine::create([
                'CashbookID' => $cb->CashbookID,
                'GLAccountID' => (int)$map->CreditGLAccountID, // counter CR
                'Description' => 'Cheques On Hand',
                'AmountDr' => 0,
                'AmountCr' => round((float)$row->Amount, 2),
            ]);

            $row->BankAccountID = $request->BankAccountID;
            $row->DepositDate = $request->DocDate;
            $row->Status = 'Deposited';
            $row->CashbookID_Deposit = $cb->CashbookID;
            $row->save();

            return redirect()->route('finance.cheques.show', $row->ChequeID)
                ->with('success', 'Cheque deposited and Cashbook posted.');
        });
    }

    // Clear a cheque:
    //  - ISSUED -> create Cashbook PAYMENT, counter DR from mapping CHQ_ISSUED_CLEAR
    //  - RECEIVED -> (already deposited) optionally just mark Cleared, or create a second receipt if you separate deposit vs clear by value date.
    public function clear(Request $request, $id)
    {
        $row = Cheque::with('bankAccount')->findOrFail($id);

        $request->validate([
            'DocDate' => 'required|date',
        ]);

        if ($row->Direction === 'ISSUED') {
            if (!in_array($row->Status, ['Issued'])) {
                return back()->with('error', 'Only Issued cheques can be cleared.');
            }

            $map = FinanceGLMapping::where('ModuleID', self::CHEQUE_MODULE_ID)
                ->where('IsActive', 1)
                ->whereHas('transactions', fn($q) => $q->where('Code', 'CHQ_ISSUED_CLEAR'))
                ->first()
                ?: FinanceGLMapping::where('ModuleID', self::CASHBOOK_MODULE_ID)
                    ->where('IsActive', 1)
                    ->whereHas('transactions', fn($q) => $q->where('Code', 'CHQ_ISSUED_CLEAR'))
                    ->first();

            if (!$map || !$map->DebitGLAccountID) {
                return back()->with('error', 'Mapping for CHQ_ISSUED_CLEAR not configured.');
            }

            return DB::transaction(function () use ($request, $row, $map) {
                // Cashbook PAYMENT from the book's bank
                $cb = new Cashbook([
                    'EntryType' => 'PAYMENT',
                    'BankAccountID' => $row->BankAccountID,
                    'DocDate' => $request->DocDate,
                    'CurrencyID' => $row->CurrencyID,
                    'ExchangeRate' => 1,
                    'Amount' => $row->Amount,
                    'AmountBase' => $row->Amount,
                    'Reference' => 'CHQ-CLR-' . $row->ChequeID,
                    'Narration' => 'Clear issued cheque ' . $row->ChequeNumber,
                    'Status' => 'Posted',
                    'SourceModule' => 'CHEQUE',
                    'SourceID' => $row->ChequeID,
                    'IsSystemGenerated' => 1,
                ]);
                $cb->save();

                CashbookLine::create([
                    'CashbookID' => $cb->CashbookID,
                    'GLAccountID' => (int)$map->DebitGLAccountID, // counter DR
                    'Description' => 'Cheque Clearance',
                    'AmountDr' => round((float)$row->Amount, 2),
                    'AmountCr' => 0,
                ]);

                $row->Status = 'Cleared';
                $row->ClearDate = $request->DocDate;
                $row->CashbookID_Clear = $cb->CashbookID;
                $row->save();

                return redirect()->route('finance.cheques.show', $row->ChequeID)
                    ->with('success', 'Issued cheque cleared & Cashbook posted.');
            });

        } else { // RECEIVED
            if (!in_array($row->Status, ['Deposited'])) {
                return back()->with('error', 'Only Deposited received cheques can be cleared.');
            }
            // Often, deposit = value date, so just mark cleared
            $row->Status = 'Cleared';
            $row->ClearDate = $request->DocDate;
            $row->save();

            return redirect()->route('finance.cheques.show', $row->ChequeID)
                ->with('success', 'Received cheque marked as Cleared.');
        }
    }

    public function bounce(Request $request, $id)
    {
        $row = Cheque::findOrFail($id);
        $request->validate([
            'DocDate' => 'required|date',
            'Reason' => 'nullable|string|max:200',
        ]);

        if ($row->Direction === 'RECEIVED' && in_array($row->Status, ['Deposited'])) {
            // Optionally book bank charge via Bank Transactions / Cashbook if you have CHQ_BOUNCE_FEE mapping
            $row->Status = 'Bounced';
            $row->BounceDate = $request->DocDate;
            $row->Narration = trim(($row->Narration ? $row->Narration . '; ' : '') . 'Bounce: ' . ($request->Reason ?? ''));
            $row->save();

            return redirect()->route('finance.cheques.show', $row->ChequeID)
                ->with('success', 'Received cheque marked as Bounced.');
        }

        if ($row->Direction === 'ISSUED' && in_array($row->Status, ['Issued'])) {
            $row->Status = 'Bounced';
            $row->BounceDate = $request->DocDate;
            $row->Narration = trim(($row->Narration ? $row->Narration . '; ' : '') . 'Bounce: ' . ($request->Reason ?? ''));
            $row->save();

            return redirect()->route('finance.cheques.show', $row->ChequeID)
                ->with('success', 'Issued cheque marked as Bounced.');
        }

        if ($row->Direction === 'ISSUED' && in_array($row->Status, ['Issued'])) {
            // ... after setting cheque Bounced:
            $leaf = \App\Models\Finance\ChequeLeaf::where('ChequeBookID', $row->ChequeBookID ?? 0)
                ->where('ChequeNumber', $row->ChequeNumber)->first();
            if ($leaf) {
                $leaf->Status = 'Bounced';
                $leaf->save();
            }
        }
        return back()->with('error', 'Cheque cannot be bounced in its current state.');
    }

    public function cancel($id)
    {
        $row = Cheque::findOrFail($id);
        if (!in_array($row->Status, ['Draft', 'Issued', 'OnHand'])) {
            return back()->with('error', 'Only Draft/Issued/OnHand cheques can be cancelled.');
        }
        $row->Status = 'Cancelled';
        $row->save();
        if (in_array($row->Status, ['Draft', 'Issued'])) {
            $row->Status = 'Cancelled';
            $row->save();

            // Mark leaf cancelled (do not return to Unused)
            if ($row->Direction === 'ISSUED') {
                $leaf = \App\Models\Finance\ChequeLeaf::where('ChequeBookID', $row->ChequeBookID ?? 0)
                    ->where('ChequeNumber', $row->ChequeNumber)->first();
                if ($leaf) {
                    $leaf->Status = 'Cancelled';
                    $leaf->CancelledOn = now()->toDateString();
                    $leaf->save();
                }
            }

            return redirect()->route('finance.cheques.show', $row->ChequeID)->with('success', 'Cheque cancelled.');
        }
    }
}
