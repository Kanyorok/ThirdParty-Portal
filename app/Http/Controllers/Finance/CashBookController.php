<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\BankAccount;
use App\Models\Finance\Cashbook;
use App\Models\Finance\CashbookLine;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceGLMapping;
use App\Models\Finance\FinanceTransactionTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashBookController extends Controller
{
    // Use your actual ModuleID for Cashbook (from t_Modules)
    private const CASHBOOK_MODULE_ID = 1103300;

    public function index()
    {
        $entries = Cashbook::with(['bankAccount', 'currency'])
            ->orderByDesc('CashbookID')
            ->paginate(25);

        return view('finance.cashbook.index', compact('entries'));
    }

    public function create()
    {
        return $this->buildCreateView(null);
    }

    public function createReceipt()
    {
        return $this->buildCreateView('RECEIPT');
    }

    public function createPayment()
    {
        return $this->buildCreateView('PAYMENT');
    }

    protected function buildCreateView(?string $presetType)
    {
        $bankAccounts = BankAccount::with(['bank', 'glAccount'])->orderBy('AccountNumber')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'Symbol', 'DecimalDigits']);
        $gls = FinanceGLAccounts::select('Id', 'GLName', 'GLCode')->orderBy('GLCode')->get();

        // Only show txn types that have active mapping for this module; fallback to all active types if none mapped yet
        $mappedTxnIds = FinanceGLMapping::where('ModuleID', self::CASHBOOK_MODULE_ID)
            ->where('IsActive', 1)
            ->pluck('TransactionTypeID')
            ->unique()
            ->filter();

        $txnQuery = FinanceTransactionTypes::where('IsActive', 1);
        $txnTypes = $mappedTxnIds->isNotEmpty()
            ? $txnQuery->whereIn('Id', $mappedTxnIds)->orderBy('Name')->get(['Id', 'Code', 'Name', 'Description'])
            : $txnQuery->orderBy('Name')->get(['Id', 'Code', 'Name', 'Description']);

        return view('finance.cashbook.create', compact('bankAccounts', 'currencies', 'presetType', 'txnTypes', 'gls'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'EntryType' => 'required|in:RECEIPT,PAYMENT',
            'BankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'DocDate' => 'required|date',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'ExchangeRate' => 'nullable|numeric',
            'Amount' => 'required|numeric|min:0.01',
            'TransactionTypeID' => 'nullable|integer|exists:t_FinanceTransactionTypes,Id',
            'UseAutoGL' => 'nullable|boolean',
            'PartyType' => 'nullable|in:VENDOR,TENANT,OTHER',
            'PartyID' => 'nullable|integer',
            'PartyName' => 'nullable|string|max:255',
            'PartyContact' => 'nullable|string|max:120',
            'PartyEmail' => 'nullable|email|max:120',
            // optional manual lines
            'lines.*.GLAccountID' => 'nullable|integer',
            'lines.*.Description' => 'nullable|string|max:300',
            'lines.*.AmountDr' => 'nullable|numeric',
            'lines.*.AmountCr' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($request) {
            $hdr = new Cashbook($request->only([
                'EntryType', 'BankAccountID', 'DocDate', 'CurrencyID', 'ExchangeRate',
                'PartyType', 'PartyID', 'PartyName', 'Reference', 'Narration', 'Amount',
            ]));

            // Optional: persist chosen type & flag if your table has these columns
            if ($this->columnExists($hdr->getTable(), 'TransactionTypeID')) {
                $hdr->TransactionTypeID = $request->input('TransactionTypeID');
            }
            if ($this->columnExists($hdr->getTable(), 'UseAutoGL')) {
                $hdr->UseAutoGL = $request->boolean('UseAutoGL', true);
            }
            if ($this->columnExists($hdr->getTable(), 'PartyContact')) {
                $hdr->PartyContact = $request->input('PartyContact');
            }
            if ($this->columnExists($hdr->getTable(), 'PartyEmail')) {
                $hdr->PartyEmail = $request->input('PartyEmail');
            }

            $hdr->AmountBase = round($hdr->Amount * ($hdr->ExchangeRate ?: 1), 2);
            $hdr->Status = 'Draft';
            $hdr->SourceModule = $request->input('SourceModule', 'MANUAL');
            $hdr->SourceID = $request->input('SourceID');
            $hdr->IsSystemGenerated = (bool)$request->input('IsSystemGenerated', 0);
            $hdr->save();

            // Lines: auto from mapping OR manual from request
            $lines = $request->input('lines', []);
            $useAuto = $request->boolean('UseAutoGL', true);
            $txnTypeId = (int)$request->input('TransactionTypeID');

            if ($useAuto && $txnTypeId) {
                $autoLines = $this->buildLinesFromMappingSimple(
                    moduleId: self::CASHBOOK_MODULE_ID,
                    txnTypeId: $txnTypeId,
                    entryType: $hdr->EntryType,
                    amount: (float)$hdr->Amount,
                    bankAccountId: (int)$hdr->BankAccountID
                );
                if (! empty($autoLines)) {
                    $lines = $autoLines; // override manual if mapping present
                }
            }

            foreach ($lines as $ln) {
                if (empty($ln['GLAccountID']) && empty($ln['AmountDr']) && empty($ln['AmountCr'])) {
                    continue;
                }
                CashbookLine::create([
                    'CashbookID' => $hdr->CashbookID,
                    'GLAccountID' => $ln['GLAccountID'] ?? null,
                    'Description' => $ln['Description'] ?? null,
                    'AmountDr' => $ln['AmountDr'] ?? 0,
                    'AmountCr' => $ln['AmountCr'] ?? 0,
                ]);
            }

            $msg = strtoupper((string)$hdr->EntryType) === 'PAYMENT'
                ? 'Cashbook payment saved as Draft.'
                : 'Cashbook entry saved as Draft.';

            return redirect()->route('cashbook.index')->with('success', $msg);
        });
    }

    public function show($id)
    {
        $entry = Cashbook::with(['bankAccount', 'currency', 'lines'])->findOrFail($id);

        return view('finance.cashbook.show', compact('entry'));
    }

    public function edit($id)
    {
        $entry = Cashbook::with('lines')->findOrFail($id);
        if ($entry->Status !== 'Draft') {
            return redirect()->route('cashbook.show', $entry->CashbookID)->with('error', 'Only Draft entries can be edited.');
        }

        $bankAccounts = BankAccount::with(['bank', 'glAccount'])->orderBy('AccountNumber')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'Symbol', 'DecimalDigits']);
        $gls = FinanceGLAccounts::select('Id', 'GLName', 'GLCode')->orderBy('GLCode')->get();

        $mappedTxnIds = FinanceGLMapping::where('ModuleID', self::CASHBOOK_MODULE_ID)
            ->where('IsActive', 1)
            ->pluck('TransactionTypeID')->unique()->filter();

        $txnQuery = FinanceTransactionTypes::where('IsActive', 1);
        $txnTypes = $mappedTxnIds->isNotEmpty()
            ? $txnQuery->whereIn('Id', $mappedTxnIds)->orderBy('Name')->get(['Id', 'Code', 'Name', 'Description'])
            : $txnQuery->orderBy('Name')->get(['Id', 'Code', 'Name', 'Description']);

        return view('finance.cashbook.edit', compact('entry', 'bankAccounts', 'currencies', 'txnTypes', 'gls'));
    }

    public function update(Request $request, $id)
    {
        $entry = Cashbook::findOrFail($id);
        if ($entry->Status !== 'Draft') {
            return redirect()->route('cashbook.show', $entry->CashbookID)->with('error', 'Only Draft entries can be updated.');
        }

        $request->validate([
            'DocDate' => 'required|date',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'ExchangeRate' => 'nullable|numeric',
            'Amount' => 'required|numeric|min:0.01',
            'TransactionTypeID' => 'nullable|integer|exists:t_FinanceTransactionTypes,Id',
            'UseAutoGL' => 'nullable|boolean',
            'PartyType' => 'nullable|in:VENDOR,TENANT,OTHER',
            'PartyID' => 'nullable|integer',
            'PartyName' => 'nullable|string|max:255',
            'PartyContact' => 'nullable|string|max:120',
            'PartyEmail' => 'nullable|email|max:120',
        ]);

        return DB::transaction(function () use ($request, $entry) {
            $entry->fill($request->only([
                'BankAccountID', 'DocDate', 'CurrencyID', 'ExchangeRate',
                'PartyType', 'PartyID', 'PartyName', 'Reference', 'Narration', 'Amount',
            ]));

            if ($this->columnExists($entry->getTable(), 'TransactionTypeID')) {
                $entry->TransactionTypeID = $request->input('TransactionTypeID');
            }
            if ($this->columnExists($entry->getTable(), 'UseAutoGL')) {
                $entry->UseAutoGL = $request->boolean('UseAutoGL', true);
            }
            if ($this->columnExists($entry->getTable(), 'PartyContact')) {
                $entry->PartyContact = $request->input('PartyContact');
            }
            if ($this->columnExists($entry->getTable(), 'PartyEmail')) {
                $entry->PartyEmail = $request->input('PartyEmail');
            }

            $entry->AmountBase = round($entry->Amount * ($entry->ExchangeRate ?: 1), 2);
            $entry->save();

            $lines = $request->input('lines', []);
            $useAuto = $request->boolean('UseAutoGL', true);
            $txnTypeId = (int)$request->input('TransactionTypeID');

            if ($useAuto && $txnTypeId) {
                $autoLines = $this->buildLinesFromMappingSimple(
                    moduleId: self::CASHBOOK_MODULE_ID,
                    txnTypeId: $txnTypeId,
                    entryType: $entry->EntryType,
                    amount: (float)$entry->Amount,
                    bankAccountId: (int)$entry->BankAccountID
                );
                if (! empty($autoLines)) {
                    $lines = $autoLines;
                }
            }

            $entry->lines()->delete();
            foreach ($lines as $ln) {
                if (empty($ln['GLAccountID']) && empty($ln['AmountDr']) && empty($ln['AmountCr'])) {
                    continue;
                }
                CashbookLine::create([
                    'CashbookID' => $entry->CashbookID,
                    'GLAccountID' => $ln['GLAccountID'] ?? null,
                    'Description' => $ln['Description'] ?? null,
                    'AmountDr' => $ln['AmountDr'] ?? 0,
                    'AmountCr' => $ln['AmountCr'] ?? 0,
                ]);
            }

            return redirect()->route('cashbook.show', $entry->CashbookID)
                ->with('success', 'Cashbook entry updated.');
        });
    }

    /**
     * AJAX: Select2 vendors from Supplier Master -> Third Parties
     */
    public function partyVendors(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $vendors = DB::table('t_SupplierMaster as sm')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->where(function ($query) use ($q) {
                $query->where('tp.ThirdPartyName', 'like', "%{$q}%")
                    ->orWhere('tp.TradingName', 'like', "%{$q}%")
                    ->orWhere('tp.RegistrationNumber', 'like', "%{$q}%")
                    ->orWhere('tp.Email', 'like', "%{$q}%")
                    ->orWhere('tp.Phone', 'like', "%{$q}%");
            })
            ->select('tp.Id as ThirdPartyId', 'tp.ThirdPartyName', 'tp.TradingName', 'tp.RegistrationNumber', 'tp.Email')
            ->orderBy('tp.ThirdPartyName')
            ->limit(20)
            ->get();

        $results = $vendors->map(function ($v) {
            $name = $v->TradingName ?: $v->ThirdPartyName;
            $text = trim($name) !== '' ? $name : 'Unknown Vendor';

            return [
                'id' => $v->ThirdPartyId,
                'text' => $text,
                'meta' => [
                    'registration' => $v->RegistrationNumber,
                    'email' => $v->Email,
                ],
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * AJAX: Select2 tenants from Tenant Master -> Third Parties
     */
    public function partyTenants(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $tenants = DB::table('t_TenantMaster as tm')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 'tm.ThirdPartyId')
            ->where(function ($query) use ($q) {
                $query->where('tp.ThirdPartyName', 'like', "%{$q}%")
                    ->orWhere('tp.TradingName', 'like', "%{$q}%")
                    ->orWhere('tp.RegistrationNumber', 'like', "%{$q}%")
                    ->orWhere('tp.Email', 'like', "%{$q}%")
                    ->orWhere('tp.Phone', 'like', "%{$q}%");
            })
            ->select('tp.Id as ThirdPartyId', 'tp.ThirdPartyName', 'tp.TradingName', 'tp.RegistrationNumber', 'tp.Email')
            ->orderBy('tp.ThirdPartyName')
            ->limit(20)
            ->get();

        $results = $tenants->map(function ($t) {
            $name = $t->TradingName ?: $t->ThirdPartyName;
            $text = trim($name) !== '' ? $name : 'Unknown Tenant';

            return [
                'id' => $t->ThirdPartyId,
                'text' => $text,
                'meta' => [
                    'registration' => $t->RegistrationNumber,
                    'email' => $t->Email,
                ],
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function destroy($id)
    {
        $entry = Cashbook::findOrFail($id);
        if ($entry->Status === 'Posted') {
            return redirect()->route('cashbook.show', $entry->CashbookID)->with('error', 'Posted entries cannot be deleted.');
        }
        $entry->lines()->delete();
        $entry->delete();

        return redirect()->route('cashbook.index')->with('success', 'Cashbook entry deleted.');
    }

    public function post($id)
    {
        $entry = Cashbook::with('lines', 'bankAccount')->findOrFail($id);
        if ($entry->Status !== 'Draft') {
            return back()->with('error', 'Only Draft entries can be posted.');
        }

        // TODO: call your GL posting service here

        $entry->Status = 'Posted';
        $entry->PostedOn = now();
        $entry->PostedBy = auth()->id();
        $entry->save();

        return redirect()->route('cashbook.show', $entry->CashbookID)->with('success', 'Entry posted.');
    }

    public function void($id)
    {
        $entry = Cashbook::findOrFail($id);
        if ($entry->Status !== 'Posted') {
            return back()->with('error', 'Only Posted entries can be voided.');
        }

        // TODO: reverse in GL accordingly (create reversing JV)

        $entry->Status = 'Voided';
        $entry->VoidedOn = now();
        $entry->VoidedBy = auth()->id();
        $entry->save();

        return redirect()->route('cashbook.show', $entry->CashbookID)->with('success', 'Entry voided.');
    }

    /**
     * AJAX mapping preview: builds counter-GL lines from FinanceGLMapping
     * GET finance/cashbook/txntype/{id}/mapping
     * Query: amount, entry_type (RECEIPT|PAYMENT), bank_gl (optional)
     */
    public function txnTypeMapping(Request $request, int $id)
    {
        $amount = (float)$request->query('amount', 0);
        $entryType = $request->query('entry_type'); // RECEIPT|PAYMENT
        $bankGl = $request->query('bank_gl');    // optional bank GL to avoid duplicating bank leg

        $map = FinanceGLMapping::with(['transactions'])
            ->where('ModuleID', self::CASHBOOK_MODULE_ID)
            ->where('TransactionTypeID', $id)
            ->where('IsActive', 1)
            ->first();

        if (! $map || ! $entryType || $amount <= 0) {
            return response()->json(['lines' => []]);
        }

        $lines = $this->linesFromSimpleMap($map, $entryType, $amount, $bankGl);

        return response()->json([
            'lines' => $lines,
            'transaction' => $map->transactions?->Name,
        ]);
    }

    /**
     * Build lines from simple mapping table:
     *  - RECEIPT: bank is DR, so counter is CR to CreditGLAccountID
     *  - PAYMENT: bank is CR, so counter is DR to DebitGLAccountID
     * Skips a line if it equals the bank GL (bank leg is handled at posting).
     */
    private function buildLinesFromMappingSimple(int $moduleId, int $txnTypeId, string $entryType, float $amount, int $bankAccountId): array
    {
        $map = FinanceGLMapping::where('ModuleID', $moduleId)
            ->where('TransactionTypeID', $txnTypeId)
            ->where('IsActive', 1)
            ->first();
        if (! $map) {
            return [];
        }

        // If BankAccount model primary key is AccountID (as expected), find() is fine.
        // Otherwise switch to where('AccountID', $bankAccountId)->first()
        $bank = BankAccount::find($bankAccountId);
        $bankGl = $bank?->GLAccountID;

        return $this->linesFromSimpleMap($map, $entryType, $amount, $bankGl);
    }

    private function linesFromSimpleMap(FinanceGLMapping $map, string $entryType, float $amount, $bankGl = null): array
    {
        $desc = $map->transactions?->Name ?? null;
        $lines = [];

        if (strtoupper($entryType) === 'RECEIPT') {
            $gl = $map->CreditGLAccountID;
            if ($gl && (empty($bankGl) || (int)$gl !== (int)$bankGl)) {
                $lines[] = [
                    'GLAccountID' => (int)$gl,
                    'Description' => $desc,
                    'AmountDr' => 0,
                    'AmountCr' => round($amount, 2),
                ];
            }
        } else { // PAYMENT
            $gl = $map->DebitGLAccountID;
            if ($gl && (empty($bankGl) || (int)$gl !== (int)$bankGl)) {
                $lines[] = [
                    'GLAccountID' => (int)$gl,
                    'Description' => $desc,
                    'AmountDr' => round($amount, 2),
                    'AmountCr' => 0,
                ];
            }
        }

        return $lines;
    }

    /** Utility: check if a column exists (to avoid errors if migration not applied yet) */
    private function columnExists(string $table, string $col): bool
    {
        static $cache = [];
        $key = $table . '.' . $col;

        if (! array_key_exists($key, $cache)) {
            $cache[$key] = DB::getSchemaBuilder()->hasColumn($table, $col);
        }

        return $cache[$key];
    }
}
