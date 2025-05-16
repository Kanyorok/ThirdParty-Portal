<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderInvitation;
use Illuminate\Http\Request;


class TenderInvitationController extends Controller
{
    public function index()
{
    return view('procurement.tendering.suppliermanagement.invitationresponsetracking.index');
}

    public function storeResponse(Request $request)
    {

        $validated = $request->validate([
            'TenderId' => 'required|integer',
            'SupplierId' => 'required|integer',
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
            'InvitationDate' => now(),
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
