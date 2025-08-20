<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceApprovalController extends Controller
{
    public function index()
    {
        // Fetch invoices pending approval
        $invoices = DB::table('t_InvoiceEntry')
            ->where('ApprovalStatus', 'Pending')
            ->orderBy('InvoiceDate', 'desc')
            ->get();

        return view('finance.accountspayable.invoiceapproval.index', compact('invoices'));
    }

    public function create($id)
    {
        // Fetch invoice by ID
        $invoice = DB::table('t_InvoiceEntry')->where('id', $id)->first();

        return view('finance.accountspayable.invoiceapproval.create', compact('invoice'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'approval_action' => 'required|in:approved,rejected',
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $invoice = DB::table('t_InvoiceEntry')->where('id', $id)->first();

        if (!$invoice) {
            return back()->withErrors(['Invoice not found.']);
        }

        // Basic business rule validation
        if ($invoice->InvoiceSource === 'goods' && !$invoice->GRNID) {
            return back()->withErrors(['Goods invoice must be linked to a GRN.']);
        }

        if ($invoice->InvoiceSource === 'service' && !$invoice->ServiceCertID) {
            return back()->withErrors(['Service invoice must be linked to a Service Certificate.']);
        }

        if ($invoice->InvoiceSource === 'exception' && !$invoice->ExceptionJustification) {
            return back()->withErrors(['Exception invoices must have a justification.']);
        }

        DB::table('t_InvoiceEntry')
            ->where('id', $id)
            ->update([
                'ApprovalStatus' => $request->approval_action === 'approved' ? 'Approved' : 'Rejected',
                'ApprovalNotes' => $request->approval_notes,
                'ApprovedBy' => auth()->user()->name ?? 'System',
                'ApprovedAt' => now(),
            ]);

        return redirect()->route('invoiceapproval.index')->with('success', 'Invoice approval processed.');
    }
}
