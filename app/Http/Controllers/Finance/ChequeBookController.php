<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Finance\BankAccount;
use App\Models\Finance\ChequeBook;
use App\Models\Finance\ChequeLeaf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChequeBookController extends Controller
{
    public function index(Request $request)
    {
        $query = ChequeBook::with('bankAccount.bank')->orderByDesc('ChequeBookID');

        // Filters
        if ($request->filled('bank_id')) {
            $query->where('BankAccountID', $request->bank_id);
        }
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') $query->where('IsActive', 1);
            if ($status === 'inactive') $query->where('IsActive', 0);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('BookName', 'like', "%$s%")
                  ->orWhere('Prefix', 'like', "%$s%")
                  ->orWhere('ChequeBookID', $s);
            });
        }

        $rows = $query->paginate($request->get('per_page', 25))->withQueryString();
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        
        // Fetch CodeDetails
        $chequeBookStatuses = CodeDetail::where('CodeID', 'ChequeBookStatus')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();

        return view('finance.cheques.books.index', compact('rows', 'bankAccounts', 'chequeBookStatuses'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        // Optional: pick first account to show an initial preview on the form
        $firstId = optional($bankAccounts->first())->AccountID;
        
        // Fetch CodeDetails
        $chequeBookSizes = CodeDetail::where('CodeID', 'ChequeBookSizes')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();
        
        return view('finance.cheques.books.create', compact('bankAccounts', 'firstId', 'chequeBookSizes'));
    }

    /**
     * AJAX: compute next Start/End/NextLeaf for a given bank account & book size.
     * GET /finance/chequebooks/next-range/{bankAccountId}?size=50
     */
    public function nextRange(Request $request, int $bankAccountId)
    {
        $size = (int)$request->query('size', 50);
        if ($size <= 0) $size = 50;

        $lastEnd = (int)ChequeBook::where('BankAccountID', $bankAccountId)->max('EndNumber');
        $start = $lastEnd > 0 ? $lastEnd + 1 : 1;
        $end = $start + $size - 1;

        return response()->json([
            'start' => $start,
            'end' => $end,
            'next_leaf' => $start,
        ]);
    }

    public function store(Request $request)
    {
        // We will auto-calc Start/End from BookSize + last book for that BankAccount
        $data = $request->validate([
            'BankAccountID' => ['required', 'integer', 'exists:t_BankAccounts,AccountID'],
            'BookName' => 'nullable|string|max:100',
            'Prefix' => 'nullable|string|max:20',
            'Suffix' => 'nullable|string|max:20',
            'BookSize' => 'required|integer|in:30,50,100',
        ]);

        return DB::transaction(function () use ($data) {
            // 1) Compute range from last book
            $size = (int)$data['BookSize'];
            $lastEnd = (int)ChequeBook::where('BankAccountID', (int)$data['BankAccountID'])->max('EndNumber');
            $start = $lastEnd > 0 ? $lastEnd + 1 : 1;
            $end = $start + $size - 1;

            // 2) Create the book ONCE
            $book = ChequeBook::create([
                'BankAccountID' => (int)$data['BankAccountID'],
                'BookName' => $data['BookName'] ?? null,
                'Prefix' => $data['Prefix'] ?? null,
                'Suffix' => $data['Suffix'] ?? null,
                'StartNumber' => $start,
                'EndNumber' => $end,
                'NextLeafNumber' => $start,
                'LeavesTotal' => $size,
                'LeavesIssued' => 0,
                'IsActive' => 1,
            ]);

            // 3) Generate leaves (chunked)
            $prefix = $book->Prefix ?? '';
            $suffix = $book->Suffix ?? '';

            $batch = [];
            for ($n = $start; $n <= $end; $n++) {
                $batch[] = [
                    'ChequeBookID' => $book->ChequeBookID,
                    'LeafNumber' => $n,
                    'ChequeNumber' => $prefix . $n . $suffix,
                    'Status' => 'Unused',
                    'CreatedOn' => now(),
                ];
                if (count($batch) === 1000) {
                    DB::table('t_ChequeLeaves')->insert($batch);
                    $batch = [];
                }
            }
            if ($batch) DB::table('t_ChequeLeaves')->insert($batch);

            return redirect()
                ->route('finance.chequebooks.show', $book->ChequeBookID)
                ->with('success', 'Cheque book created and leaves generated.');
        });
    }

    public function show(Request $request, $id)
    {
        $row = ChequeBook::with('bankAccount.bank')->findOrFail($id);

        $leavesQuery = ChequeLeaf::where('ChequeBookID', $row->ChequeBookID);
        if ($request->filled('status')) $leavesQuery->where('Status', $request->status);
        if ($request->filled('q')) {
            $q = $request->q;
            $leavesQuery->where(function ($qq) use ($q) {
                $qq->where('ChequeNumber', 'like', "%{$q}%")
                    ->orWhere('LeafNumber', (int)$q);
            });
        }
        $leaves = $leavesQuery->orderBy('LeafNumber')->paginate(50)->appends($request->query());

        return view('finance.cheques.books.show', compact('row', 'leaves'));
    }

    public function edit($id)
    {
        $row = ChequeBook::findOrFail($id);
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        
        // Fetch CodeDetails
        $chequeBookSizes = CodeDetail::where('CodeID', 'ChequeBookSizes')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();
            
        $chequeBookStatuses = CodeDetail::where('CodeID', 'ChequeBookStatus')
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get();
        
        return view('finance.cheques.books.edit', compact('row', 'bankAccounts', 'chequeBookSizes', 'chequeBookStatuses'));
    }

    public function update(Request $request, $id)
    {
        $row = ChequeBook::findOrFail($id);
        $data = $request->validate([
            'BankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'BookName' => 'nullable|string|max:100',
            'Prefix' => 'nullable|string|max:20',
            'Suffix' => 'nullable|string|max:20',
            'StartNumber' => 'required|integer|min:1',
            'EndNumber' => 'required|integer|gt:StartNumber',
            'NextLeafNumber' => 'required|integer|gte:StartNumber|lte:EndNumber',
            'IsActive' => 'nullable|boolean',
        ]);

        $row->fill([
            'BankAccountID' => (int)$data['BankAccountID'],
            'BookName' => $data['BookName'] ?? null,
            'Prefix' => $data['Prefix'] ?? null,
            'Suffix' => $data['Suffix'] ?? null,
            'StartNumber' => (int)$data['StartNumber'],
            'EndNumber' => (int)$data['EndNumber'],
            'NextLeafNumber' => (int)$data['NextLeafNumber'],
            'IsActive' => (int)($data['IsActive'] ?? $row->IsActive),
        ]);
        $row->LeavesTotal = (int)$data['EndNumber'] - (int)$data['StartNumber'] + 1;
        $row->save();

        return redirect()->route('finance.chequebooks.index')->with('success', 'Cheque book updated.');
    }

    public function destroy($id)
    {
        $row = ChequeBook::findOrFail($id);

        $inUse = DB::table('t_ChequeLeaves')
            ->where('ChequeBookID', $row->ChequeBookID)
            ->whereIn('Status', ['Reserved', 'Issued', 'Cleared', 'Bounced', 'Cancelled'])
            ->exists();

        if ($inUse) {
            return back()->with('error', 'Cannot delete: some leaves are already used.');
        }

        return DB::transaction(function () use ($row) {
            DB::table('t_ChequeLeaves')->where('ChequeBookID', $row->ChequeBookID)->delete();
            $row->delete();
            return redirect()->route('finance.chequebooks.index')->with('success', 'Cheque book deleted.');
        });
    }
}
