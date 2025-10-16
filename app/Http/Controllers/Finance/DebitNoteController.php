<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCDNotes;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceInvoiceEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebitNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableView, FinanceCDNotes::class);
        $invoices = FinanceInvoice::select('Id','InvoiceNumber','InvoiceTitle','TotalAmount')->get();

        $query = FinanceCDNotes::with('invoiceDebit:Id,InvoiceNumber')
            ->select('Id', 'CDNumber', 'NoteType', 'InvoiceRefNo', 'NoteDate', 'NoteAmount', 'Description','ApprovalStatus')
            ->where('NoteType','debit');

        if ($request->filled('cd_number')) {
            $query->where('CDNumber', 'like', '%'.$request->cd_number.'%');
        }
        if ($request->filled('invoice_number')) {
            $invNum = $request->invoice_number;
            $query->whereHas('invoiceDebit', function($q) use ($invNum) {
                $q->where('InvoiceNumber', 'like', '%'.$invNum.'%');
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('NoteDate', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('NoteDate', '<=', $request->date_to);
        }
        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('ApprovalStatus', $request->approval_status);
        }
        if ($request->filled('amount_min')) {
            $query->where('NoteAmount', '>=', (float)$request->amount_min);
        }

        $sortField = $request->sort_by ?? 'NoteDate';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int)($request->per_page ?? 10);
        $notes = $query->paginate($perPage)->withQueryString();

        $approvalStatuses = FinanceCDNotes::where('NoteType','debit')->distinct()->pluck('ApprovalStatus')->filter()->unique()->values();

        return view('finance.accountsreceivable.debitnote.index', compact('notes','invoices','approvalStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableCreate, FinanceCDNotes::class);

        $invoices = FinanceInvoice::select('Id', 'InvoiceNumber', 'InvoiceTitle', 'TotalAmount')->where('ApprovalStatus', 'posted')
            ->get();

        return view('finance.accountsreceivable.debitnote.create', compact('invoices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableCreate, FinanceCDNotes::class);

        $validated = $request->validate([
            'InvoiceRefNo' => 'required|exists:t_FinanceInvoices,Id',
            'NoteDate' => 'required|date',
            'NoteAmount' => 'required|numeric|min:0.00',
            'Description' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // $cdNumber = str_pad(rand(0,999999), 6, '0', STR_PAD_LEFT);

            if ($request->NoteType == 'Credit') {
                $notes = FinanceCDNotes::create([
                    'NoteType' => 'credit',
                    // 'CDNumber'=>$cdNumber,
                    'InvoiceRefNo' => $validated['InvoiceRefNo'],
                    'NoteDate' => $validated['NoteDate'],
                    'NoteAmount' => $validated['NoteAmount'],
                    'Description' => $validated['Description'],
                    'CreatedBy' => Auth::Id(),
                    'ModifiedBy' => Auth::Id(),
                ]);
            } else {
                $notes = FinanceCDNotes::create([
                    'NoteType' => 'debit',
                    // 'CDNumber'=>$cdNumber,
                    'InvoiceRefNo' => $validated['InvoiceRefNo'],
                    'NoteDate' => $validated['NoteDate'],
                    'NoteAmount' => $validated['NoteAmount'],
                    'Description' => $validated['Description'],
                    'CreatedBy' => Auth::Id(),
                    'ModifiedBy' => Auth::Id(),
                ]);
            }
            activity()
                ->performedOn($notes)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Note Sucessfully:' . $notes->id);
            DB::commit();
            return redirect()->route('debitnote.index')->with('success', 'Debit Note created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            return back()->with('error', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $note = FinanceCDNotes::with([
            // AP invoice relations (FinanceInvoiceEntry)
            'invoice',
            'invoice.supplier',
            'invoice.order:Id,OrderNo',
            'invoice.currency:Id,Code',
            // AR invoice relations (FinanceInvoice)
            'invoiceDebit',
            'invoiceDebit.customer',
            'invoiceDebit.currency:Id,Code',
            // audit
            'createdBy:Id,Name',
            'modifiedBy:Id,Name'
        ])->findOrFail($id);

        return view('finance.accountspayable.creditnote.show', compact('note'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableUpdate, FinanceCDNotes::class);

        $validated = $request->validate([
            'InvoiceRefNo' => 'required|exists:t_FinanceInvoices,Id',
            'NoteDate' => 'required|date',
            'NoteAmount' => 'required|numeric|min:0.00',
            'Description' => 'nullable|string',
            'NoteType' => 'required|in:credit,debit',
        ]);

        DB::beginTransaction();
        try {
            $note = FinanceCDNotes::where('Id', $id)->update([
                'InvoiceRefNo' => $validated['InvoiceRefNo'],
                'NoteDate' => $validated['NoteDate'],
                'NoteAmount' => $validated['NoteAmount'],
                'Description' => $validated['Description'],
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->performedOn(new FinanceCDNotes())
                ->causedBy(Auth::id())
                ->withProperties(['action' => 'update'])
                ->log('Updated Note successfully: ');

            DB::commit();
            return redirect()->route('debitnote.index')->with('success', 'Debit Note updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            return back()->with('error', $th->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
