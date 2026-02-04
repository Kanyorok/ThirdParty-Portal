<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderSubmissionController extends Controller
{
    public function index(): View
    {
        $this->authorize(\App\Enums\Core\PermissionEnum::BidSubmissionRead->value);
        $submissions = BidSubmission::with([
            'submissionMode',
            'createdByUser',
            'supplier.supplierMaster.party',
        ])
            ->orderBy('CreatedOn', 'desc')
            ->get();

        return view('procurement.tendering.suppliermanagement.bidsubmission.index', compact('submissions'));
    }

    public function create(Request $request)
    {
        $this->authorize(\App\Enums\Core\PermissionEnum::BidSubmissionWrite->value);
        $currencies = \App\Models\Core\Currency::all();
        // Exclude tenders that already have submissions & filter by Published status
        $tenders = Tender::select('Id', 'TenderNo', 'Title', 'TenderType', 'SubmissionDeadline')
            ->where('Status', \App\Enums\TenderStatusEnum::Published->value)
            ->whereNot('Status', \App\Enums\TenderStatusEnum::Awarded->value)
            ->whereNot('Status', \App\Enums\TenderStatusEnum::Closed->value)
            ->where('ApprovalStatus', '!=', \App\Enums\TenderApprovalStatusEnum::REJECTED->value)
            ->where('SubmissionDeadline', '>', now()) // Filter by deadline
            ->withCount(['invitedSuppliers', 'submissions']) // Get counts for filtering
            ->get()
            ->filter(function ($tender) {
                // For restricted tenders, check if all invited suppliers have submitted
                if ($tender->TenderType === \App\Enums\TenderTypeEnum::Restricted) {
                    // Check if submissions count >= invited count (simple check, or use diff if needed)
                    // Note: This assumes 1 submission per supplier.
                    // If multiple submissions allowed per supplier, better to check unique supplier IDs in submissions.
                    // But typically 1 active submission.
                    // Let's rely on counts for performance, or check more deeply if needed.
                    // Actually, let's filter: if invited count > 0 and submissions >= invited, hide it.
                    if ($tender->invited_suppliers_count > 0 && $tender->submissions_count >= $tender->invited_suppliers_count) {
                        return false;
                    }
                }

                return true;
            });

        // Initialize suppliers (loaded via AJAX)
        $suppliers = [];

        $submissionModes = DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->get(['ID', 'Description']);

        return view('procurement.tendering.suppliermanagement.bidsubmission.create', compact('tenders', 'suppliers', 'submissionModes', 'currencies'));
    }

    public function view($Id)
    {
        $submission = BidSubmission::findOrFail($Id);

        return view('procurement.tendering.suppliermanagement.bidsubmission.view', compact('submission'));
    }

    public function edit($Id)
    {
        $submission = BidSubmission::findOrFail($Id);
    }

    public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'tender_ref' => 'required|string|max:255',
            'supplier_name' => 'required', // Now contains ID, removed string constraint to be safe or keep if ID is string
            'submission_mode' => 'required|string|max:255',
            'received_at' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'bid_files' => 'required|file|mimes:zip,pdf|max:10240', // Max 10MB
            'currency' => 'required|string|exists:t_Currencies,Code',
            'bid_amount' => 'required|numeric|min:0',
            'validity_period' => 'required|integer|min:1',
            'delivery_period' => 'required|integer|min:1',
            'payment_terms' => 'nullable|string',
        ]);

        // Check if tender exists and submission is within deadline
        $tender = Tender::where('TenderNo', $request->tender_ref)->first();
        if (! $tender) {
            return redirect()->back()->withErrors(['tender_ref' => 'Invalid Tender Reference.']);
        }

        $receivedOnTime = true;
        if ($tender->SubmissionDeadline && \Carbon\Carbon::parse($request->received_at)->gt($tender->SubmissionDeadline)) {
            // For manual submission, we might want to warn or allow with flag.
            // Current logic blocks. Assuming strict enforcement.
            // If we want to allow "Late" submissions (as user implied), we should remove the blocking return.
            // BUT user said "Received says no", implying the system marked it as late/no.
            // If I remove the block, they can submit late.
            // Let's Keep the block for now but calculate the flag correctly for valid range.
            // Actually, if it's strictly blocked, ReceivedOnTime is always true.
            // But let's calculate it to be robust.
            return redirect()->back()->withErrors(['received_at' => 'Cannot record submission. The received date is past the tender submission deadline (' . $tender->SubmissionDeadline->format('d/m/Y H:i') . ').'])->withInput();
        }

        // Map submission_mode to t_CodeDetails ID
        $submissionModeId = DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->where('Description', $request->submission_mode)
            ->value('ID');

        if (! $submissionModeId) {
            return redirect()->back()->withErrors(['submission_mode' => 'Invalid submission mode selected.']);
        }

        // Get supplier ID directly from selection
        $supplier = Supplier::find($request->supplier_name);

        if ($supplier) {
            // Check if tender is Restricted and if supplier is invited
            if ($tender->TenderType === \App\Enums\TenderTypeEnum::Restricted) {
                $isInvited = $tender->invitedSuppliers()->where('t_Suppliers.Id', $supplier->Id)->exists();
                if (! $isInvited) {
                    return redirect()->back()->withErrors(['supplier_name' => 'This supplier is not invited to this restricted tender.'])->withInput();
                }
            }

            $existingSubmission = BidSubmission::where('TenderRef', $request->tender_ref)
                ->where('SupplierId', $supplier->Id)
                ->exists();

            if ($existingSubmission) {
                return redirect()->back()->withErrors(['supplier_name' => 'A submission for this tender and supplier already exists.'])->withInput();
            }
        } else {
            return redirect()->back()->withErrors(['supplier_name' => 'Selected supplier not found.'])->withInput();
        }

        DB::beginTransaction();

        try {
            // Get Supplier Name for display/redundancy (as per schema)
            $supplierName = $supplier->supplierMaster->thirdParty->TradingName ?? $supplier->supplierMaster->thirdParty->ThirdPartyName;

            // Create bid submission record
            $bidSubmission = BidSubmission::create([
                'TenderRef' => $request->tender_ref,
                'SupplierName' => $supplierName,
                'SupplierId' => $supplier->Id,
                'SubmissionMode' => $submissionModeId,
                'ReceivedAt' => $request->received_at,
                'RecordedBy' => $request->recorded_by,
                'Remarks' => $request->remarks,
                'SubmissionSource' => 'manual',
                'DocumentsAccessible' => false, // Sealed until bid opening
                'Currency' => $request->currency,
                'BidAmount' => $request->bid_amount,
                'ValidityPeriod' => $request->validity_period,
                'DeliveryPeriod' => $request->delivery_period,
                'PaymentTerms' => $request->payment_terms,
                'ReceivedOnTime' => $receivedOnTime, // Explicitly set timeliness
                'CreatedBy' => $request->user()->Id,
                'ModifiedBy' => $request->user()->Id,
            ]);

            // Handle file upload
            if ($request->hasFile('bid_files')) {
                $bidSubmission->newDocument(
                    \App\Enums\Core\ModulesEnum::Procurement,
                    $request->file('bid_files'),
                    [\App\Enums\Core\PermissionEnum::BidSubmissionRead->value],
                    $request->user()
                );
            }

            DB::commit();

            return redirect()->route('tendersubmissions.index')
                ->with('success', 'Bid submission recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Failed to record bid submission: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function getInvitedSuppliers($tenderId)
    {
        // Try finding by TenderNo first (as it's passed from Select2 value)
        $tender = Tender::where('TenderNo', $tenderId)->first();

        // Fallback to ID if not found
        if (! $tender) {
            $tender = Tender::find($tenderId);
        }

        if (! $tender) {
            return response()->json(['error' => 'Tender not found'], 404);
        }

        // Get IDs of suppliers who already submitted for this tender
        $submittedSupplierIds = BidSubmission::where('TenderRef', $tender->TenderNo)
            ->pluck('SupplierId')
            ->toArray();

        $query = Supplier::query();

        // If Restricted, only show invited suppliers
        if ($tender->TenderType === \App\Enums\TenderTypeEnum::Restricted) {
            $query->whereIn('Id', $tender->invitedSuppliers()->pluck('t_Suppliers.Id'));
        }

        // Exclude suppliers who have already submitted
        $query->whereNotIn('Id', $submittedSupplierIds);

        // Fetch suppliers with details
        $suppliers = $query->with(['supplierMaster.party'])->get();

        // Map to format expected by frontend (Id, SupplierName)
        $data = $suppliers->unique('Id')->map(function ($supplier) {
            $name = $supplier->supplierMaster->party->TradingName
                ?? $supplier->supplierMaster->party->ThirdPartyName
                ?? 'Unknown Supplier';

            return [
                'Id' => $supplier->Id,
                'SupplierName' => $name,
            ];
        })->values();

        return response()->json($data);
    }

    private function resolveSupplier(): ?Supplier
    {
        $user = Auth::user();

        if (property_exists($user, 'ThirdPartyId') && $user->ThirdPartyId) {
            return Supplier::whereHas(
                'supplierMaster',
                fn ($q) =>
                $q->where('ThirdPartyId', $user->ThirdPartyId)
            )->first();
        }

        $tpu = DB::table('t_ThirdPartyUsers')->where('Id', $user->Id)->first();

        if ($tpu?->ThirdPartyId) {
            return Supplier::whereHas(
                'supplierMaster',
                fn ($q) =>
                $q->where('ThirdPartyId', $tpu->ThirdPartyId)
            )->first();
        }

        return null;
    }
}
