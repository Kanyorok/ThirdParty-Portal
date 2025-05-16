<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderInvitation;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;

class TenderResponseController extends Controller
{
    //

    public function index()
    {
        $invitations = TenderInvitation::all();
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.index', compact('invitations'));
    }

    public function create(){
        $tenders = Tender::select('Id','TenderNo')->get();
        $suppliers = Supplier::select('Id','SupplierName')->get();
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.create', compact('tenders', 'suppliers'));
    }
     public function storeResponse(Request $request)
    {
        $validated = $request->validate([
            'TenderId' => 'required|integer|exists:t_Tenders,Id',
            'SupplierId' => 'required|integer|exists:t_Suppliers,Id',
            'ResponseStatus' => 'required|in:Pending,Accepted,Declined',
            'DeclineReason' => 'nullable|string',
            'ConfirmationAttachment' => 'nullable|file|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('ConfirmationAttachment')) {
            $path = $request->file('ConfirmationAttachment')->store('attachments', 'public');
        }

        TenderInvitation::create([
            'TenderId' => $validated['TenderId'],
            'SupplierId' => $validated['SupplierId'],
            'InvitationDate' => now(), // Or get from DB if already exists
            'ResponseStatus' => $validated['ResponseStatus'],
            'ResponseDate' => now(),
            'DeclineReason' => $validated['DeclineReason'] ?? null,
            'ConfirmationAttachment' => $path,
            'CreatedBy' => $request->user()->Id,
            'ModifiedBy' => $request->user()->Id,
        ]);

        return redirect()->route('tenderresponse.index')->with('success', 'Your response has been recorded.');
    }
}
