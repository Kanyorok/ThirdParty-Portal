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
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableView, FinanceCDNotes::class);

        $invoices = FinanceInvoice::select('Id','InvoiceNumber','InvoiceTitle','TotalAmount')
            ->get();

        $notes = FinanceCDNotes::with('invoiceDebit:Id,InvoiceNumber')
            ->select('Id', 'CDNumber', 'NoteType', 'InvoiceRefNo', 'NoteDate', 'NoteAmount', 'Description','ApprovalStatus')
            ->where('NoteType','debit')->latest()->get();
        return view('finance.accountsreceivable.debitnote.index', compact('notes','invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        $this->authorize(PermissionEnum::FinanceAccountsReceivableCreate, FinanceCDNotes::class);

         $invoices = FinanceInvoice::select('Id','InvoiceNumber','InvoiceTitle','TotalAmount')->where('ApprovalStatus','posted')
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
            'InvoiceRefNo'=> 'required|exists:t_FinanceInvoices,Id',
            'NoteDate'=> 'required|date',
            'NoteAmount'=> 'required|numeric|min:0.00',
            'Description'=> 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // $cdNumber = str_pad(rand(0,999999), 6, '0', STR_PAD_LEFT);

            if ($request->NoteType =='Credit') {
                $notes = FinanceCDNotes::create([
                    'NoteType'=> 'credit',
                    // 'CDNumber'=>$cdNumber,
                    'InvoiceRefNo'=> $validated['InvoiceRefNo'],
                    'NoteDate'=> $validated['NoteDate'],
                    'NoteAmount'=> $validated['NoteAmount'],
                    'Description'=> $validated['Description'],
                    'CreatedBy'          =>Auth::Id(),
                    'ModifiedBy'         => Auth::Id(),
                ]);
            } else {
                $notes = FinanceCDNotes::create([
                    'NoteType'=> 'debit',
                    // 'CDNumber'=>$cdNumber,
                    'InvoiceRefNo'=> $validated['InvoiceRefNo'],
                    'NoteDate'=> $validated['NoteDate'],
                    'NoteAmount'=> $validated['NoteAmount'],
                    'Description'=> $validated['Description'],
                    'CreatedBy'          =>Auth::Id(),
                    'ModifiedBy'         => Auth::Id(),
                ]);
            }
            activity()
                ->performedOn($notes)
                ->causedBy(Auth::user())
                ->withProperties(['action' =>'create'])
                ->log('Created Note Sucessfully:'. $notes->id);
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
            'invoice',
            'invoice.supplier',
            'invoice.order:Id,OrderNo',
            'invoice.currency:Id,Code',
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
            'NoteDate'     => 'required|date',
            'NoteAmount'   => 'required|numeric|min:0.00',
            'Description'  => 'nullable|string',
            'NoteType'     => 'required|in:credit,debit',
        ]);

        DB::beginTransaction();
        try {
            $note=FinanceCDNotes::where('Id',$id)->update([
                'InvoiceRefNo'=> $validated['InvoiceRefNo'],
                'NoteDate'    => $validated['NoteDate'],
                'NoteAmount'  => $validated['NoteAmount'],
                'Description' => $validated['Description'],
                'ModifiedBy'  => Auth::id(),
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
