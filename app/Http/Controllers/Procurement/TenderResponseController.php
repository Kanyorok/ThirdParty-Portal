<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\TenderApprovalStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderInvitation;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;

class TenderResponseController extends Controller
{
    public function index()
    {
        $invitations = TenderInvitation::all();

        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.index', compact('invitations'));
    }

    public function create()
    {
        $tenders = Tender::where('ApprovalStatus', TenderApprovalStatusEnum::APPROVED)
            ->select('Id', 'TenderNo', 'Title')
            ->get();


        $suppliers = Supplier::with('supplierMaster.thirdParty')
            ->whereNull('DeletedOn')
            ->get()
            ->map(function ($supplier) {
                return (object) [
                    'Id' => $supplier->Id,
                    'SupplierName' => $supplier->supplierMaster->thirdParty->TradingName
                        ?? $supplier->supplierMaster->thirdParty->ThirdPartyName,
                ];
            });

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

        $tender = Tender::findOrFail($validated['TenderId']);
        if ($tender->SubmissionDeadline && now()->greaterThan($tender->SubmissionDeadline)) {
            return back()->with('error', 'The submission deadline for this tender has passed. Response cannot be recorded.');
        }

        $path = null;
        if ($request->hasFile('ConfirmationAttachment')) {
            $path = $request->file('ConfirmationAttachment')->store('attachments', 'public');
        }

        $invitation = TenderInvitation::where('TenderId', $validated['TenderId'])
            ->where('SupplierId', $validated['SupplierId'])
            ->first();

        if ($invitation) {
            // Prevent overwriting if already responded
            if ($invitation->ResponseStatus !== 'Pending') {
                return back()->with('error', 'A response ' . $invitation->ResponseStatus . ' has already been recorded for this supplier.');
            }

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
        $tender = Tender::with(['invitedSuppliers.supplierMaster.thirdParty'])
            ->findOrFail($tenderId);

        $suppliers = $tender->invitedSuppliers->map(function ($supplier) {
            return [
                'Id' => $supplier->Id,
                'SupplierName' => $supplier->supplierMaster->thirdParty->TradingName
                    ?? $supplier->supplierMaster->thirdParty->ThirdPartyName,
            ];
        });

        return response()->json($suppliers);
    }
}
