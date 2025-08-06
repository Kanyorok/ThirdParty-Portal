<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCDNotes;
use App\Models\Finance\FinanceInvoiceEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Financial;

class CreditNoteController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceCDNotes::class);

        $invoices = FinanceInvoiceEntry::select('Id','InvoiceNumber')
            ->get();

        $notes = FinanceCDNotes::with('invoice:Id,InvoiceNumber')
            ->select('Id', 'CDNumber', 'NoteType', 'InvoiceRefNo', 'NoteDate', 'NoteAmount', 'Description')
            ->get();
        return view('finance.accountspayable.creditnote.index', compact('notes','invoices'));
    }

    public function create(){
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceCDNotes::class);

        $invoices = FinanceInvoiceEntry::select('Id','InvoiceNumber')
            ->get();      

        return view('finance.accountspayable.creditnote.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceCDNotes::class);

        $validated = $request->validate([
            'InvoiceRefNo'=> 'required|exists:t_FinanceInvoiceEntry,Id',
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
                return redirect()->route('creditnote.index')->with('success', 'Credit Note created successfully.');
            } catch (\Throwable $th) {
                DB::rollBack();
                return back()->with('error', $th->getMessage());
            }
    }
}

