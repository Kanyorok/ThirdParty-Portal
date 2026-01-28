<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use App\Services\Procurement\EncryptedBidDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenderSubmissionController extends Controller
{
    public function index()
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

    public function create()
    {
        $this->authorize(\App\Enums\Core\PermissionEnum::BidSubmissionWrite->value);
        // Exclude tenders that already have submissions
        $tenders = Tender::select('TenderNo', 'Title')
            ->doesntHave('submissions')
            ->get();

        // Fix: Get supplier names from the related ThirdParty table
        $suppliers = Supplier::select('t_Suppliers.Id')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->join('t_ThirdParties', 't_SupplierMaster.ThirdPartyId', '=', 't_ThirdParties.Id')
            ->selectRaw('t_Suppliers.Id, COALESCE(t_ThirdParties.TradingName, t_ThirdParties.ThirdPartyName) as SupplierName')
            ->whereNull('t_Suppliers.DeletedOn')
            ->get();

        $submissionModes = DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->get(['ID', 'Description']);

        return view('procurement.tendering.suppliermanagement.bidsubmission.create', compact('tenders', 'suppliers', 'submissionModes'));
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
            'supplier_name' => 'required|string|max:255',
            'submission_mode' => 'required|string|max:255',
            'received_at' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'bid_files' => 'required|file|mimes:zip,pdf|max:10240', // Max 10MB
        ]);

        // Map submission_mode to t_CodeDetails ID
        $submissionModeId = DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->where('Description', $request->submission_mode)
            ->value('ID');

        if (! $submissionModeId) {
            return redirect()->back()->withErrors(['submission_mode' => 'Invalid submission mode selected.']);
        }

        // Get supplier ID from supplier name
        $supplier = Supplier::join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->join('t_ThirdParties', 't_SupplierMaster.ThirdPartyId', '=', 't_ThirdParties.Id')
            ->where(function ($query) use ($request) {
                $query->where('t_ThirdParties.TradingName', $request->supplier_name)
                    ->orWhere('t_ThirdParties.ThirdPartyName', $request->supplier_name);
            })
            ->whereNull('t_Suppliers.DeletedOn')
            ->select('t_Suppliers.Id')
            ->first();

        if ($supplier) {
            $existingSubmission = BidSubmission::where('TenderRef', $request->tender_ref)
                ->where('SupplierId', $supplier->Id)
                ->exists();

            if ($existingSubmission) {
                return redirect()->back()->withErrors(['supplier_name' => 'A submission for this tender and supplier already exists.'])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Create bid submission record
            $bidSubmission = BidSubmission::create([
                'TenderRef' => $request->tender_ref,
                'SupplierName' => $request->supplier_name,
                'SupplierId' => $supplier?->Id,
                'SubmissionMode' => $submissionModeId,
                'ReceivedAt' => $request->received_at,
                'RecordedBy' => $request->recorded_by,
                'Remarks' => $request->remarks,
                'SubmissionSource' => 'manual',
                'DocumentsAccessible' => false, // Sealed until bid opening
                'CreatedBy' => $request->user()->Id,
                'ModifiedBy' => $request->user()->Id,
            ]);

            // Store encrypted documents if uploaded
            if ($request->hasFile('bid_files')) {
                $encryptedDocs = EncryptedBidDocumentService::storeEncryptedBidDocuments(
                    $bidSubmission,
                    [$request->file('bid_files')],
                    $request->user()
                );

                // Update submission with encrypted document info
                $bidSubmission->update([
                    'EncryptedDocuments' => json_encode($encryptedDocs),
                    'ModifiedBy' => $request->user()->Id,
                ]);
            }

            DB::commit();

            return redirect()->route('tendersubmission.index')
                ->with('success', 'Manual submission recorded successfully. Documents are encrypted and sealed until bid opening ceremony.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withErrors(['error' => 'Failed to store submission: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
