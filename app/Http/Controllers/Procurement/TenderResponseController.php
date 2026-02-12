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
            ->whereNot('Status', \App\Enums\TenderStatusEnum::OpeningInProgress->value)
            ->whereNot('Status', \App\Enums\TenderStatusEnum::Awarded->value)
            ->where('SubmissionDeadline', '>', now())
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
            'SupplierId' => 'required|integer|exists:t_SupplierMaster,Id',
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

        // Resolve Master ID to a Child Supplier ID
        // Preference: Active -> Prequalified -> Any
        $supplierMasterId = $validated['SupplierId'];
        $childSupplier = Supplier::where('SupplierMasterId', $supplierMasterId)
            ->orderByDesc('Active_Status')
            ->first();

        if (! $childSupplier) {
            return back()->with('error', 'No valid supplier profile found for this master record.');
        }

        // DUPLICATE CHECK: Check across ALL child IDs for this master
        $allChildIds = Supplier::where('SupplierMasterId', $supplierMasterId)->pluck('Id');

        $existingResponse = TenderInvitation::where('TenderId', $validated['TenderId'])
            ->whereIn('SupplierId', $allChildIds)
            ->where('ResponseStatus', '!=', 'Pending')
            ->first();

        if ($existingResponse) {
            // Block duplicate response
            return back()->with('error', 'A response (' . $existingResponse->ResponseStatus . ') has already been recorded for this supplier.');
        }

        // Find specific invitation for the chosen child (or create new)
        // Check if there is a pending invitation for ANY child, use that one to update
        $invitation = TenderInvitation::where('TenderId', $validated['TenderId'])
           ->whereIn('SupplierId', $allChildIds)
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
            // Create new response record using the resolved Child ID
            // This ensures Portal visibility
            TenderInvitation::create([
                'TenderId' => $validated['TenderId'],
                'SupplierId' => $childSupplier->Id,
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

        // Get IDs of suppliers who have already responded (not Pending)
        // These are IDs from t_Suppliers (Child IDs)
        $respondedChildIds = TenderInvitation::where('TenderId', $tenderId)
            ->where('ResponseStatus', '!=', 'Pending')
            ->pluck('SupplierId')
            ->toArray();

        // Resolve Child IDs to Master IDs to exclude from the dropdown
        $respondedMasterIds = [];
        if (! empty($respondedChildIds)) {
            $respondedMasterIds = Supplier::whereIn('Id', $respondedChildIds)
               ->pluck('SupplierMasterId')
               ->unique()
               ->toArray();
        }

        // Query SupplierMaster (Parent record) to get both Active/Approved AND Prequalified suppliers
        // Note: SupplierMaster connects to ThirdParty via ThirdPartyId
        $query = \App\Models\ThirdParty\SupplierMaster::whereNull('DeletedOn')
            ->where(function ($q) {
                // Include if Approved (Active) OR IsPrequalified
                $q->where('ApprovalStatus', \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Approved)
                  ->orWhere('IsPrequalified', true);
            })
            ->whereNotIn('Id', $respondedMasterIds) // Exclude MASTERS who have alrady responded via any child
            ->with(['thirdParty']);

        // RESTRICTED TENDER: Only allow explicitly invited suppliers
        // TenderSupplier stores SupplierID which is SupplierMaster.Id
        if ($tender->TenderType === \App\Enums\TenderTypeEnum::Restricted) {
            $invitedSupplierIds = \App\Models\Procurement\TenderSupplier::where('TenderID', $tenderId)
                ->pluck('SupplierID')
                ->toArray();

            $query->whereIn('Id', $invitedSupplierIds);
        }

        $suppliers = $query->get()
            ->map(function ($supplier) {
                $name = $supplier->thirdParty->TradingName
                    ?? $supplier->thirdParty->ThirdPartyName
                    ?? 'Unknown Supplier';

                return [
                    'Id' => $supplier->Id,
                    'SupplierName' => $name,
                ];
            })
            ->unique('Id') // Start by unique ID
            ->unique('SupplierName') // Ensure unique names in dropdown
            ->values();

        // Log::info('Invited Suppliers Count for Tender ' . $tenderId . ': ' . $suppliers->count());

        return response()->json($suppliers);
    }
}
