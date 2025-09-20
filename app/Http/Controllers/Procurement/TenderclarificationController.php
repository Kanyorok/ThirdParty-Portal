<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\VendorClarifications;
use App\Models\Procurement\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenderclarificationController extends Controller
{
    /**
     * Display all clarifications with improved filtering
     */
    public function index(Request $request)
    {
        $query = VendorClarifications::with(['tenderID', 'vendorID']);

        // Filter by tender if specified
        if ($request->filled('tender_id')) {
            $query->where('TenderID', $request->tender_id);
        }

        // Filter by status
        $status = $request->get('status', 'all');
        if ($status === 'pending') {
            $query->whereNull('Answer');
        } elseif ($status === 'answered') {
            $query->whereNotNull('Answer');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('Question', 'LIKE', "%{$search}%")
                  ->orWhere('Answer', 'LIKE', "%{$search}%");
            });
        }

        // Order by latest questions first, but prioritize unanswered ones
        $clarifications = $query->orderByRaw('CASE WHEN Answer IS NULL THEN 0 ELSE 1 END')
                                ->orderBy('QuestionDate', 'desc')
                                ->paginate(20);

        // Get statistics
        $stats = [
            'total' => VendorClarifications::whereNull('DeletedOn')->count(),
            'pending' => VendorClarifications::whereNull('Answer')->whereNull('DeletedOn')->count(),
            'answered' => VendorClarifications::whereNotNull('Answer')->whereNull('DeletedOn')->count(),
            'public' => VendorClarifications::where('ISPUBLISHEDTOALL', true)->whereNull('DeletedOn')->count(),
        ];

        // Get active tenders for filter dropdown
        $tenders = Tender::select('Id', 'TenderNo', 'Title')
                         ->where('Status', 'pb') // published tenders
                         ->orderBy('CreatedOn', 'desc')
                         ->take(20)
                         ->get();

        return view('procurement.tendering.suppliermanagement.clarificationhandling.index', 
                    compact('clarifications', 'stats', 'tenders', 'status'));
    }

    /**
     * Show pending clarifications dashboard
     */
    public function pending()
    {
        $pendingClarifications = VendorClarifications::with(['tenderID', 'vendorID'])
            ->whereNull('Answer')
            ->whereNull('DeletedOn')
            ->orderBy('QuestionDate', 'asc') // Oldest first for urgent attention
            ->paginate(15);

        // Add supplier names
        $pendingClarifications->getCollection()->transform(function($clarification) {
            $supplierName = 'Unknown Supplier';
            if ($clarification->vendorID && $clarification->vendorID->thirdParty) {
                $supplierName = $clarification->vendorID->thirdParty->TradingName 
                             ?? $clarification->vendorID->thirdParty->ThirdPartyName;
            }
            $clarification->supplierName = $supplierName;
            $clarification->daysPending = now()->diffInDays($clarification->QuestionDate);
            return $clarification;
        });

        return view('procurement.tendering.suppliermanagement.clarificationhandling.pending', 
                    compact('pendingClarifications'));
    }

    /**
     * Show form to respond to a specific clarification
     */
    public function create($clarification_id)
    {
        $clarification = VendorClarifications::with(['tenderID', 'vendorID'])
                                             ->findOrFail($clarification_id);

        // Get supplier name
        $supplierName = 'Unknown Supplier';
        if ($clarification->vendorID && $clarification->vendorID->thirdParty) {
            $supplierName = $clarification->vendorID->thirdParty->TradingName 
                         ?? $clarification->vendorID->thirdParty->ThirdPartyName;
        }
        $clarification->supplierName = $supplierName;

        return view('procurement.tendering.suppliermanagement.clarificationhandling.create', 
                    compact('clarification'));
    }

    /**
     * Update clarification with response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'clarification_id' => 'required|exists:t_VendorClarifications,ClarificationID',
            'answer' => 'required|string|min:10|max:2000',
            'is_published_to_all' => 'sometimes|boolean',
        ]);

        $clarification = VendorClarifications::findOrFail($validated['clarification_id']);

        // Prevent double answering
        if ($clarification->Answer) {
            return redirect()->back()->withInput()->with('error', 'This clarification has already been answered.');
        }

        try {
            $isPublished = $request->boolean('is_published_to_all');
            $userId = optional($request->user())->Id ?? 1;

            $clarification->Answer = trim($validated['answer']);
            $clarification->AnswerDate = now();
            $clarification->ISPUBLISHEDTOALL = $isPublished;
            $clarification->ModifiedBy = $userId;
            $clarification->ModifiedOn = now();
            $clarification->save();

            // Log the response
            Log::info('Clarification answered', [
                'clarification_id' => $clarification->ClarificationID,
                'tender_id' => $clarification->TenderID,
                'answered_by' => $userId,
                'is_public' => $isPublished
            ]);

            return redirect()->route('tenderclarification.index')
                            ->with('success', 'Clarification response submitted successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to save clarification response', [
                'clarification_id' => $validated['clarification_id'],
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save response. Please try again.');
        }
    }

    /**
     * Edit form for modifying existing response
     */
    public function edit(Request $request)
    {
        $clarificationId = $request->query('clarification_id');
        
        if (!$clarificationId) {
            return redirect()->route('tenderclarification.index')
                           ->with('error', 'Clarification ID is required.');
        }

        $clarification = VendorClarifications::with(['tenderID', 'vendorID'])
                                             ->findOrFail($clarificationId);

        // Get supplier name
        $supplierName = 'Unknown Supplier';
        if ($clarification->vendorID && $clarification->vendorID->thirdParty) {
            $supplierName = $clarification->vendorID->thirdParty->TradingName 
                         ?? $clarification->vendorID->thirdParty->ThirdPartyName;
        }
        $clarification->supplierName = $supplierName;

        return view('procurement.tendering.suppliermanagement.clarificationhandling.edit', 
                    compact('clarification'));
    }

    /**
     * Bulk action for marking clarifications as public/private
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'clarification_ids' => 'required|array',
            'clarification_ids.*' => 'exists:t_VendorClarifications,ClarificationID',
            'action' => 'required|in:make_public,make_private,delete'
        ]);

        $count = 0;
        
        switch ($request->action) {
            case 'make_public':
                $count = VendorClarifications::whereIn('ClarificationID', $request->clarification_ids)
                                            ->whereNotNull('Answer')
                                            ->update(['ISPUBLISHEDTOALL' => true]);
                break;
                
            case 'make_private':
                $count = VendorClarifications::whereIn('ClarificationID', $request->clarification_ids)
                                            ->update(['ISPUBLISHEDTOALL' => false]);
                break;
                
            case 'delete':
                $count = VendorClarifications::whereIn('ClarificationID', $request->clarification_ids)
                                            ->update([
                                                'DeletedBy' => $request->user()->Id,
                                                'DeletedOn' => now()
                                            ]);
                break;
        }

        return redirect()->back()->with('success', "Successfully processed {$count} clarifications.");
    }
}
