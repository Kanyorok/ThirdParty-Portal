<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Requisition\RequisitionRequest;
use App\Models\Procurement\Requisitions;
use App\Services\Procurement\Requisition\RequisitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequisitionsController extends Controller
{
    public function __construct(protected RequisitionService $service)
    {
        $this->middleware('ajax')->except(['index', 'show', 'create']);
        // $this->authorizeResource(Requisitions::class); // Uncomment if using authorization
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('procurement.requisitions.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
//        dd($request->user());
        return view('procurement.requisitions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequisitionRequest $request): JsonResponse
    {
//        dd($request->user());
        try {
            $validatedData = $request->validated();
            $branch = $validatedData['Branch'];
            $department = $validatedData['Department'];
            $remarks = $validatedData['Remarks'];
            $category = $validatedData['Category'];
            $actor = $request->user();


            if (!$actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $requisitionAdd = $this->service->addRequisition(
                $branch,
                $department,
                $remarks,
                $category,
                $actor
            );

            if ($requisitionAdd) {
                return response()->json([
                    'message' => 'Requisition added successfully',
                    'requisitionId' => $requisitionAdd
                ], 200);
            }

            // Log failure with details
            \Log::error('Failed to create requisition.', [
                'branch' => $branch,
                'department' => $department,
                'remarks' => $remarks,
                'category' => $category,
                'user_id' => $actor,
                'service_response' => $requisitionAdd,
            ]);

            return response()->json([
                'message' => 'Failed to create requisition',
                'details' => 'See server logs for more information.'
            ], 500);

        } catch (\Throwable $e) {
            \Log::error('Exception occurred while creating requisition.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create requisition',
                'error' => $e->getMessage()
            ], 500);
        }
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
}
