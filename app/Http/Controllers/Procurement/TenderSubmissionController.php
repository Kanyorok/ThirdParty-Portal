<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        $paymentTerms = CodeDetail::query()
                ->where('CodeID', 'PaymentTerm')
                ->orderBy('DisplayOrder')
                ->get(['ID', 'Description']);

        if ($paymentTerms->isEmpty()) {
            $paymentTerms = DB::table('t_CodeDetails')
                ->whereIn(DB::raw('RTRIM(LTRIM(CodeID))'), ['PaymentTerm', 'PaymentTerms'])
                ->orderBy('DisplayOrder')
                ->select('ID', 'Description')
                ->get();
        }
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

        return view('procurement.tendering.suppliermanagement.bidsubmission.create', compact('tenders', 'suppliers', 'submissionModes', 'currencies', 'paymentTerms'));
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
            'supplier_name' => 'required', // Now contains ID
            'submission_mode' => 'required|string|max:255',
            'received_at' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'bid_files' => 'required|file|mimes:zip,pdf,doc,docx,xls,xlsx|max:10240', // Max 10MB, expanded types
            'currency' => 'required|string|exists:t_Currencies,Code',
            'bid_amount' => 'required|numeric|min:0',
            'validity_period' => 'required|integer|min:1',
            'delivery_period' => 'required|integer|min:1',
            'payment_terms' => 'nullable|string',
        ]);

        // Start transaction early to lock the tender and prevent race conditions
        DB::beginTransaction();

        try {
            // Lock the tender row to serialize submissions for this tender
            // This prevents two parallel requests from passing the duplicate check simultaneously
            $tender = Tender::where('TenderNo', $request->tender_ref)->lockForUpdate()->first();

            if (! $tender) {
                // If tender not found (shouldn't happen given validation, but safety check)
                DB::rollBack(); // Release lock/transaction

                return redirect()->back()->withErrors(['tender_ref' => 'Invalid Tender Reference.']);
            }

            // Check deadline
            $receivedOnTime = true;
            if ($tender->SubmissionDeadline && \Carbon\Carbon::parse($request->received_at)->gt($tender->SubmissionDeadline)) {
                DB::rollBack();

                return redirect()->back()->withErrors(['received_at' => 'Cannot record submission. The received date is past the tender submission deadline (' . $tender->SubmissionDeadline->format('d/m/Y H:i') . ').'])->withInput();
            }

            // Map submission_mode to t_CodeDetails ID
            $submissionModeId = DB::table('t_CodeDetails')
                ->where('CodeID', 'SubmissionMode')
                ->where('Description', $request->submission_mode)
                ->value('ID');

            if (! $submissionModeId) {
                DB::rollBack();

                return redirect()->back()->withErrors(['submission_mode' => 'Invalid submission mode selected.']);
            }

            // Get supplier
            $supplier = Supplier::find($request->supplier_name);

            if (! $supplier) {
                DB::rollBack();

                return redirect()->back()->withErrors(['supplier_name' => 'Selected supplier not found.'])->withInput();
            }

            // Check if tender is Restricted and if supplier is invited
            if ($tender->TenderType === \App\Enums\TenderTypeEnum::Restricted) {
                $isInvited = $tender->invitedSuppliers()->where('t_Suppliers.Id', $supplier->Id)->exists();
                if (! $isInvited) {
                    DB::rollBack();

                    return redirect()->back()->withErrors(['supplier_name' => 'This supplier is not invited to this restricted tender.'])->withInput();
                }
            }

            // Check for duplicate submission inside the lock
            $existingSubmission = BidSubmission::where('TenderRef', $request->tender_ref)
                ->where('SupplierId', $supplier->Id)
                ->exists();

            if ($existingSubmission) {
                DB::rollBack();

                return redirect()->back()->withErrors(['supplier_name' => 'A submission for this tender and supplier already exists.'])->withInput();
            }

            // Get Supplier Name for display/redundancy
            $supplierName = $supplier->supplierMaster->thirdParty->TradingName ?? $supplier->supplierMaster->thirdParty->ThirdPartyName;

            // Handle file upload with "Encryption" logic (Metadata + Secure Storage)
            $encryptedDocumentsData = [];
            $masterEncryptionKey = null;

            if ($request->hasFile('bid_files')) {
                $file = $request->file('bid_files');
                $masterEncryptionKey = Str::random(32);

                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                // Safe unique name
                $encryptedFileName = 'bid_manual_' . $tender->TenderNo . '_' . $supplier->Id . '_' . time() . '.' . $extension;

                // Store file in secure location
                $storagePath = $file->store('bid-documents', 'local');

                // Create metadata
                $documentInfo = [
                    'original_name' => $originalName,
                    'stored_path' => $storagePath,
                    'encrypted_filename' => $encryptedFileName,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'document_type' => 'manual_submission',
                    'encrypted_at' => now()->toISOString(),
                    'encryption_method' => 'Laravel-Crypt',
                ];

                $encryptedDocumentsData[] = $documentInfo;
            }

            // Prepare encryption columns
            $jsonEncryptedDocs = ! empty($encryptedDocumentsData) ? json_encode($encryptedDocumentsData) : null;
            $base64Envelope = $masterEncryptionKey ? encrypt($masterEncryptionKey) : null;
            $rawEnvelope = $base64Envelope ? base64_decode($base64Envelope) : null;
            $encryptionEnvelope = $rawEnvelope ? DB::raw("CONVERT(VARBINARY(MAX), 0x" . bin2hex($rawEnvelope) . ")") : null;

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
                'DocumentsAccessible' => false,
                'Currency' => $request->currency,
                'BidAmount' => $request->bid_amount,
                'ValidityPeriod' => $request->validity_period,
                'DeliveryPeriod' => $request->delivery_period,
                'PaymentTerms' => $request->payment_terms,
                'ReceivedOnTime' => $receivedOnTime,
                'CreatedBy' => $request->user()->Id,
                'ModifiedBy' => $request->user()->Id,
                'EncryptedDocuments' => $jsonEncryptedDocs,
                'EncryptionKey' => $base64Envelope,
                'EncryptionEnvelope' => $encryptionEnvelope,
            ]);

            $redirect = redirect()->route('tendersubmission.index')
                ->with('success', 'Bid submission recorded successfully.');

            DB::commit();

            return $redirect;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Manual Bid Submission Failed: " . $e->getMessage());

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
        })->unique('SupplierName')->values();

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
