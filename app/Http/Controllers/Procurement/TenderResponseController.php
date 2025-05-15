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
        $tenders = Tender::select('TenderNo')->get();
        $suppliers = Supplier::select('SupplierName')->get();
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.create', compact('tenders', 'suppliers'));
    }
     public function storeResponse(Request $request)
    {
        $validated = $request->validate([
            'TenderID' => 'required|integer',
            'SupplierID' => 'required|integer',
            'ResponseStatus' => 'required|in:Pending,Accepted,Declined',
            'DeclineReason' => 'nullable|string',
            'ConfirmationAttachment' => 'nullable|file|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('ConfirmationAttachment')) {
            $path = $request->file('ConfirmationAttachment')->store('attachments', 'public');
        }

        TenderInvitation::create([
            'TenderID' => $validated['TenderID'],
            'SupplierID' => $validated['SupplierID'],
            'InvitationDate' => now(), // Or get from DB if already exists
            'ResponseStatus' => $validated['ResponseStatus'],
            'ResponseDate' => now(),
            'DeclineReason' => $validated['DeclineReason'] ?? null,
            'ConfirmationAttachment' => $path,
            'CreatedBy' => $request->user()->Id,
            'ModifiedBy' => $request->user()->Id,
        ]);

        return redirect()->back()->with('success', 'Your response has been recorded.');
    }
}
