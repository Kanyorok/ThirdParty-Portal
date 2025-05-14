<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenderInvitation;

class TenderResponseController extends Controller 
{
    //

    public function index()
    {
        $invitations = TenderInvitation::all();
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.index', compact('invitations'));
    }

    public function create(){
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.create');
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
        ]);

        return redirect()->back()->with('success', 'Your response has been recorded.');
    }
}
