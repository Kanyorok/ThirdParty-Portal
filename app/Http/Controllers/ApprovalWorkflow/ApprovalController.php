<?php

namespace App\Http\Controllers\ApprovalWorkflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'document_id' => 'required|integer',
            'notes' => 'nullable|string'
        ]);

        $actor = $request->user();
        if (!$actor) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Begin transaction
        DB::connection('sqlsrv')->beginTransaction();

        try {
            $result = DB::connection('sqlsrv')->select(
                "EXEC p_ProcessWorkflowAction
             @ActionType = 'approve',
             @Source = ?,
             @SourceID = ?,
             @UserID = ?,
             @UserName = ?,
             @Notes = ?,
             @StatusColumn = ?",
                [
                    $validated['document_type'],
                    $validated['document_id'],
                    $actor->Id,
                    $actor->Name,
                    $validated['notes'] ?? null,
                    $this->getStatusColumn($validated['document_type'])
                ]
            );

            // Commit the transaction if everything is successful
            DB::connection('sqlsrv')->commit();

            return response()->json($result[0]);

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::connection('sqlsrv')->rollBack();

            // Log the error for debugging
            \Log::error('Approval failed: ' . $e->getMessage(), [
                'document_type' => $validated['document_type'],
                'document_id' => $validated['document_id'],
                'user_id' => $actor->Id
            ]);

            return response()->json([
                'Status' => 'ERROR',
                'Message' => 'Approval process failed. Please try again.'
                // Don't expose raw error messages in production
            ], 500);
        }
    }

    private function getStatusColumn($documentType)
    {
        return match ($documentType) {
            't_PurchaseOrders' => 'POStatus',
            't_Requisitions' => 'ReqStatus',
            default => 'Status'
        };
    }
}
