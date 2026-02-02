<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCDNotes;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Services\Finance\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::CreditNoteView, FinanceCDNotes::class);

        $invoices = FinanceInvoiceEntry::select('Id', 'InvoiceNumber')->get();

        $query = FinanceCDNotes::with('invoice:Id,InvoiceNumber')
            ->select('Id', 'CDNumber', 'NoteType', 'InvoiceRefNo', 'NoteDate', 'NoteAmount', 'Description', 'ApprovalStatus')
            ->where('NoteType', 'credit');

        if ($request->filled('cd_number')) {
            $query->where('CDNumber', 'like', '%' . $request->cd_number . '%');
        }

        if ($request->filled('invoice_number')) {
            $invNum = $request->invoice_number;
            $query->whereHas('invoice', function ($q) use ($invNum) {
                $q->where('InvoiceNumber', 'like', '%' . $invNum . '%');
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

        $approvalStatuses = FinanceCDNotes::where('NoteType', 'credit')->distinct()->pluck('ApprovalStatus')->filter()->unique()->values();

        return view('finance.accountspayable.creditnote.index', compact('notes', 'invoices', 'approvalStatuses'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::CreditNoteCreate, FinanceCDNotes::class);

        $invoices = FinanceInvoiceEntry::select('Id', 'InvoiceNumber')->where('ApprovalStatus', 'posted')
            ->get();

        return view('finance.accountspayable.creditnote.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::CreditNoteCreate, FinanceCDNotes::class);

        $validated = $request->validate([
            'InvoiceRefNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
            'NoteDate' => 'required|date',
            'NoteAmount' => 'required|numeric|min:0.00',
            'Description' => 'required|string',
        ]);

        DB::beginTransaction();

        try {

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

            return redirect()->route('creditnote.index')->with('success', 'Credit Note created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', $th->getMessage());
        }
    }

    public function show(int $id)
    {
        $this->authorize(PermissionEnum::CreditNoteView, FinanceCDNotes::class);
        $note = FinanceCDNotes::with([
            'invoice',
            'invoice.thirdParty',
            'invoice.order:Id,OrderNo',
            'invoice.currency:Id,Code',
            'createdBy:Id,Name',
            'modifiedBy:Id,Name',
        ])->findOrFail($id);

        return view('finance.accountspayable.creditnote.show', compact('note'));
    }

    public function approve(Request $request, int $id, TransactionService $svc)
    {

        $validated = $request->validate([
            'Reason' => 'required|string|max:255',
        ]);

        //Get the NoteTYPE
        $noteType = FinanceCDNotes::find($id)->NoteType;
        if ($noteType === 'credit') {
            $trxType = 18;
        } elseif ($noteType === 'debit') {
            $trxType = 19;
        }
        // Configure your module + transaction type mapping IDs
        // Make sure these exist in t_Modules and t_FinanceTransactionTypes
        $MODULE_ID = 1100000; // Finance module
        $TRANSACTION_TYPEID = $trxType;    // "AP Invoice"

        try {
            return DB::transaction(function () use ($id, $validated, $svc, $MODULE_ID, $TRANSACTION_TYPEID) {

                // Load the invoice with the same relations, and lock row for update
                $invoice = FinanceCDNotes::with([
                    'invoice:Id,InvoiceNumber,SupplierID',
                    'invoice.supplier:Id',
                    'invoice.order:Id,OrderNo',
                    'invoice.currency:Id,Code',
                    'createdBy:Id,Name',
                    'modifiedBy:Id,Name',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                // Guard: already posted?
                if (in_array($invoice->ApprovalStatus, ['posted', 'rejected'], true)) {
                    $apStatus = ucfirst($invoice->ApprovalStatus);

                    return back()->with('error', "Note {$invoice->CDNumber} is already {$apStatus}.");
                }
                $noteType = FinanceCDNotes::find($id)->NoteType;
                // Build payload for TransactionService (service does idempotency)
                $payload = [
                    'ModuleID' => $MODULE_ID,
                    'ThirdPartyID' => optional($invoice->invoice)->SupplierID,
                    'TransactionTypeID' => $TRANSACTION_TYPEID,
                    'TransactionType' => ucfirst((string)($noteType ?? '')) . ' Note',
                    'ReferenceNumber' => (string)($invoice->CDNumber ?? ''),
                    'TransactionDate' => $invoice->NoteDate ?? now()->toDateString(),
                    // Amounts
                    'Amount' => (float)($invoice->NoteAmount ?? 0),                 // note amount
                    'TaxAmount' => (float)(optional($invoice->invoice)->TaxAmount ?? 0), // consider swapping to note tax if you store it
                    // Org context
                    'BranchID' => (int)session('LoginBranchId', 1),
                    'DepartmentID' => optional($invoice->invoice)->DepartmentID ?? null,
                    // Currency
                    'CurrencyID' => optional($invoice->invoice)->CurrencyID ?? 1,
                    'CurrencyCode' => optional(optional($invoice->invoice)->currency)->Code ?? 'KES',
                    'ExchangeRate' => (float)(optional($invoice->invoice)->ExchangeRate ?? 1),
                    // Descriptions
                    'Narration' => trim(
                        (string)($invoice->Description ?? '') . ' ' . (string)($validated['Reason'] ?? '')
                    ),
                    'SourceTable' => 't_FinanceCDNotes',
                    'SystemDescription' => sprintf('%s Note %s', ucfirst((string)($noteType ?? '')), (string)($invoice->CDNumber ?? '')),
                    // Optional overrides if needed:
                    // 'DebitGLAccountID'  => 5001,
                    // 'CreditGLAccountID' => 3001,
                    // 'TaxGLAccountID'    => 2101,
                ];
                // Post via mapping; TransactionService handles:
                $result = $svc->postFromTypeMapping($payload);

                // Update invoice approval status if posted (or keep as-is if service reported 'exists')
                if (in_array($result['status'], ['success', 'exists'], true)) {
                    $invoice->update([
                        'ApprovalStatus' => 'posted',
                        'ApprovalReason' => $validated['Reason'],
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                }

                // Prefer a user-friendly flash message
                $message = $result['status'] === 'exists'
                    ? "Invoice {$invoice->InvoiceNumber} was already posted (idempotent)."
                    : ($result['message'] ?? "Note {$invoice->InvoiceNumber} posted successfully.");

                $flashKey = $result['status'] === 'success' ? 'success' : 'info';

                activity('Transaction Posting')
                    ->performedOn(new FinanceCDNotes())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Posted from Account payable Invoice'])
                    ->log('Posted Transaction from Accounts Payable Invoice');

                return back()->with($flashKey, $message);
            });
        } catch (\Throwable $e) {
            // Log if you want: Log::error('AP approve error', ['id'=>$id, 'err'=>$e->getMessage()])
            return $e->getMessage();

            return back()->with('error', "Approval/Post failed: " . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'Reason' => 'required|string|max:1000',
        ]);

        try {
            return DB::transaction(function () use ($validated, $id) {
                // Lock the row for update to avoid race conditions
                $invoice = FinanceCDNotes::findOrFail($id);

                // If already processed, prevent duplicate rejection
                if (in_array($invoice->ApprovalStatus, ['posted', 'rejected'], true)) {
                    $apStatus = ucfirst($invoice->ApprovalStatus);

                    return back()->with('error', "Note {$invoice->CDNumber} is already {$apStatus}.");
                }
                // Update status & reason
                $invoice->update([
                    'ApprovalStatus' => 'rejected',
                    'ApprovalReason' => $validated['Reason'],
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                activity('Transaction Posting')
                    ->performedOn(new FinanceCDNotes())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Rejected from CDNotes'])
                    ->log('Rejected Transaction from Credit/Debit Note');

                return back()->with('success', "Note {$invoice->CDNumber} rejected successfully.");
            });
        } catch (\Throwable $e) {
            Log::error('AP reject error', ['id' => $id, 'err' => $e->getMessage()]);

            return back()->with('error', "Approval/Post failed: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::CreditNoteDelete, FinanceCDNotes::class);

        $note = FinanceCDNotes::findOrFail($id);

        if ($note->ApprovalStatus === 'posted') {
            return back()->with('error', 'Cannot delete a posted note.');
        }

        $note->DeletedBy = Auth::id();
        $note->save();
        $note->delete();

        activity('Transaction Posting')
            ->performedOn($note)
            ->causedBy(Auth::id())
            ->withProperties(['action' => 'delete'])
            ->log('Deleted Note: ' . $note->CDNumber);

        return back()->with('success', 'Credit/Debit Note deleted successfully.');
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::CreditNoteUpdate, FinanceCDNotes::class);

        $note = FinanceCDNotes::findOrFail($id);

        $validated = $request->validate([
            'InvoiceRefNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
//            'InvoiceRefNo'=> 'required',
            'NoteDate' => 'required|date',
            'NoteAmount' => 'required|numeric|min:0.00',
            'Description' => 'string',
        ]);

        DB::beginTransaction();

        try {
            $note->update([
//                'InvoiceRefNo' => $validated['InvoiceRefNo'],
                'NoteDate' => $validated['NoteDate'],
                'NoteAmount' => $validated['NoteAmount'],
                'Description' => $validated['Description'],
                'ModifiedBy' => Auth::id(),
            ]);

            activity('Transaction Posting')
                ->performedOn($note)
                ->causedBy(Auth::id())
                ->withProperties(['action' => 'update'])
                ->log('Updated Note: ' . $note->CDNumber);

            DB::commit();

            return back()->with('success', 'Note updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', $th->getMessage());
        }
    }
}
