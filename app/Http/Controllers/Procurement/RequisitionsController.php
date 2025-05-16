<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Requisition\RequisitionRequest;
use App\Models\Procurement\Requisitions;
use App\Services\Procurement\Requisition\RequisitionItemService;
use App\Services\Procurement\Requisition\RequisitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequisitionsController extends Controller
{
    public function __construct(protected RequisitionService $service,protected RequisitionItemService $itemService)
    {
        $this->middleware('ajax')->except(['index', 'show', 'create']);
        $this->authorizeResource(Requisitions::class); // Uncomment if using authorization
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//        return view('procurement.requisitions.approval');
        try {
            $details = $this->service->fetchRequisition();
            return view('procurement.requisitions.approval', compact('details'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }

    public function approvalList(){
        return view("procurement.requisitions.approval");
//        try {
//            $details = $this->service->fetchRequisition();
//            return view('procurement.requisition.approval', compact('details'));
//        } catch (\Exception $e) {
//            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
//        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $details = $this->service->fetchRequisition();
            // if ($details ) {
            return view('procurement.requisitions.create', compact('details'));
        // /}
            // else{  return view('procurement.requisitions.create', ['details' => []]);
            // }
        } catch (\Exception $e) {
            Log::error('Create page failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }

//        return view('procurement.requisitions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequisitionRequest $request): JsonResponse
    {
//       dd($request->user());
        try {
            $validatedData = $request->validated();


            $actor = $request->user();
            if (!$actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }


            $requisitionAdd = $this->service->addRequisition(
                $validatedData['Branch'],
                $validatedData['Department'],
                $validatedData['Remarks'],
                $validatedData['Category'],
                $actor
            );

            if ($requisitionAdd['status'] === 'success') {
                return response()->json([
                    'message' => $requisitionAdd['message'],
                    'route' =>route('requisition.create')
                ], 200);
            }

            // Log failure with details
            \Log::error('Failed to create requisition.', [
                'input' => $validatedData,
                'user_id' => $actor->id ?? null,
                'service_response' => $requisitionAdd,
            ]);

            return response()->json([
                'message' => $requisitionAdd['message'],
                'error' => $requisitionAdd['error'] ?? 'Unknown error'
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


    public function getRequisitions(): JsonResponse{
        try{
            $details = $this->service->fetchRequisition();
            return response()->json([
                'success' => true,
                'data' => $details,
            ]);}
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view', Requisitions::query()->findOrFail($id));
        try {

            $details = $this->itemService->getRequisitionRelatedItems($id);
            return view('procurement.requisitions.show', compact('details'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
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
