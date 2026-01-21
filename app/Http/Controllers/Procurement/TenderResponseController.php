<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderInvitation;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use App\Enums\TenderApprovalStatusEnum;
use App\Enums\TenderStatusEnum;

class TenderResponseController extends Controller
{
    //

    public function index()
    {
        $invitations = TenderInvitation::all();
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.index', compact('invitations'));
    }

    public function create(){
        // Get only active/approved tenders that are not closed
        
        $tenders = Tender::where('Status', TenderStatusEnum::Published)
             ->where('ApprovalStatus', TenderApprovalStatusEnum::APPROVED)
             ->whereDate('SubmissionDeadline', '>=', now())
             // Exclude tenders that already have an Accepted or Declined response.
             // Note: This hides the tender if *any* supplier has responded, per user request.
             ->whereDoesntHave('invitations', function($q) {
                 $q->whereIn('ResponseStatus', ['Accepted', 'Declined']);
             })
             ->select('Id', 'TenderNo', 'Title')
             ->get();

        // Pass empty suppliers list initially - they will be loaded via AJAX
        $suppliers = [];

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

        $invitation = TenderInvitation::where('TenderId', $validated['TenderId'])
            ->where('SupplierId', $validated['SupplierId'])
            ->first();

        if ($invitation) {
            $invitation->update([
                'ResponseStatus' => $validated['ResponseStatus'],
                'ResponseDate' => now(),
                'DeclineReason' => $validated['DeclineReason'] ?? null,
                'ConfirmationAttachment' => $path ?? $invitation->ConfirmationAttachment, // Preserve old file if no new one
                'ModifiedBy' => $request->user()->Id,
            ]);
        } else {
            // Create new response record if it doesn't exist (e.g. public tender self-nomination)
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
        }

        return redirect()->route('tenderresponse.index')->with('success', 'Your response has been recorded.');
    }

    public function getInvitedSuppliers($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);

        if ($tender->TenderType === \App\Enums\TenderTypeEnum::Open) { // Public Tender
             // List all approved and prequalified suppliers
             $suppliers = Supplier::where('Active_Status', 1)
                ->with('supplierMaster.thirdParty')
                ->get()
                ->map(function($supplier) {
                    return [
                        'Id' => $supplier->Id,
                        'SupplierName' => $supplier->supplierMaster->thirdParty->TradingName 
                            ?? $supplier->supplierMaster->thirdParty->ThirdPartyName
                    ];
                });
        } else {
            // Restricted Tender - load active invitations
             // Re-using the relationship approach from original code for safety
             $suppliers = $tender->invitedSuppliers->map(function($supplier) {
                return [
                    'Id' => $supplier->Id,
                    'SupplierName' => $supplier->supplierMaster->thirdParty->TradingName 
                        ?? $supplier->supplierMaster->thirdParty->ThirdPartyName
                ];
            });
        }

        return response()->json($suppliers);
    }
}
