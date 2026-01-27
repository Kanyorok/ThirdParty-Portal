<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\ApprovalEnum;
use App\Http\Controllers\Controller;
use App\Models\Legal\LegalDocument;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LegalContractController extends Controller
{
    public function __construct(protected ApprovalWorkflow $workflow)
    {
    }

    /**
     * Display a listing of legal contracts.
     */
    public function index()
    {
        $contracts = LegalDocument::whereNull('DeletedOn')->orderBy('CreatedOn', 'desc')->get();
        // Check if user can approve contracts generally or specific ones
        // This logic depends on how canApproveModel is implemented, usually it checks permissions
        // We can pass a flag to the view if needed

        return view('legal.contracts.index', compact('contracts'));
    }

    /**
     * Show the form for creating a new contract.
     */
    public function create()
    {
        return view('legal.contracts.create');
    }

    /**
     * Store a newly created contract in storage.
     */
    public function store(Request $request)
    {
        // Placeholder implementation - ensure Initial Status is set
        // $contract = LegalDocument::create($request->all());
        // $contract->ReviewStatus = ApprovalEnum::Pending->value;
        // $contract->save();

        return redirect()->route('legal.contracts.index')
            ->with('success', 'Contract created successfully');
    }

    /**
     * Display the specified contract.
     */
    public function show(string $id)
    {
        $contract = LegalDocument::findOrFail($id);
        $canApprove = $this->workflow->canApproveModel($contract);

        return view('legal.contracts.show', compact('contract', 'canApprove'));
    }

    /**
     * Show the form for editing the specified contract.
     */
    public function edit(string $id)
    {
        $contract = LegalDocument::findOrFail($id);

        return view('legal.contracts.edit', compact('contract'));
    }

    /**
     * Update the specified contract in storage.
     */
    public function update(Request $request, string $id)
    {
        // Placeholder implementation
        return redirect()->route('legal.contracts.index')
            ->with('success', 'Contract updated successfully');
    }

    /**
     * Remove the specified contract from storage.
     */
    public function destroy(string $id)
    {
        $contract = LegalDocument::findOrFail($id);
        $contract->delete();

        return redirect()->route('legal.contracts.index')
            ->with('success', 'Contract deleted successfully');
    }

    /**
     * Submit contract for approval
     */
    public function submitForApproval(Request $request, int $id): JsonResponse
    {
        $document = LegalDocument::findOrFail($id);

        return $this->workflow->submit($document, $request->user());
    }

    /**
     * Approve contract
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $document = LegalDocument::findOrFail($id);

        return $this->workflow->approve($document, $request->user(), $request->input('comments'));
    }

    /**
     * Reject contract
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $document = LegalDocument::findOrFail($id);

        return $this->workflow->reject($document, $request->user(), $request->input('comments'));
    }
}
