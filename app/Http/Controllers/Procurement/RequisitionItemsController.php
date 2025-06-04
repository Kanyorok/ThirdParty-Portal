<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Requisition\RequisitionItemRequest;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\Requisitions;
use App\Services\Procurement\Items\ItemService;
use App\Services\Procurement\Requisition\RequisitionItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequisitionItemsController extends Controller
{
    public function __construct(protected RequisitionItemService $service,protected ItemService $itemService)
    {

        $this->middleware('ajax')->except(['index', 'create', 'show']);
       // $this->authorizeResource(RequisitionLines::class);
    }
    /**
     * Display a listing of the resource.
     *
     */

//    public function getItems(string $type): JsonResponse
//    {
////        $this->authorize('view',RequisitionLines::class);
//        try{
//            $items = $this->itemService->getItemByType($type);
//            return response()->json([
//                'success' => true,
//                'data' => $items,
//            ]);}
//        catch(\Exception $e){
//            return response()->json([
//                'success' => false,
//                'message' => 'Failed to fetch items.',
//                'error' => $e->getMessage(),
//            ], 500);
//        }
//    }

    public function getItems(string $type, Request $request): JsonResponse
    {
//        $this->authorize('view',RequisitionLines::class);
        try{
            $requisitionId = $request->query('requisition_id');
            $items = $this->itemService->getItemByType($type,$requisitionId);
            return response()->json([
                'success' => true,
                'data' => $items,
            ]);}
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getItemDetails($item): JsonResponse
    {
        try{

            if (empty($item)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item parameter is required',
                ], 400);
            }

            $details = $this->itemService->getItemDetails($item);
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

    public function getRequisitionItems(): JsonResponse
    {
        try{
            $details = $this->service->getRequisitionItems();
            return response()->json([
                'success' => true,
                'data' => $details,
            ]);}
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inventory.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $this->authorize('viewAny', RequisitionLine::class);
        // use for requisitionItem approval

//        return view ('procurement.requisitionItems.approval');
//
        try {
            $details = $this->service->getRequisitionItems();
            return view('procurement.requisitionItems.priorityList', compact('details'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $this->authorize('create', RequisitionLine::class);
        try {
            $details = $this->service->getRequisitionItems();
            return view('procurement.requisitionItems.create', compact('details'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(RequisitionItemRequest $request): JsonResponse
    {
        $this->authorize('create', RequisitionLine::class);
//        dd($request->all());
        try {
            $validatedData = $request->validated();


            $actor = $request->user();
            if (!$actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }


            $requisitionAddLines = $this->service->addRequisitionLines(
                $validatedData['RequisitionID'],
                $validatedData['Item'],
                $validatedData['Quantity'],
//                $validatedData['NeededBy'],
                $validatedData['Urgency'],
                $actor
            );

            if ($requisitionAddLines['status'] === 'success') {
                return response()->json([
                    'message' => $requisitionAddLines['message'],
                    'route' => route('requisition.show', $validatedData['RequisitionID'])
                ], 200);
            }

            // Log failure with details
            Log::error('Failed to create requisitionLines **.', [
                'input' => $validatedData,
                'user_id' => $actor->id ?? null,
                'service_response' => $requisitionAddLines,
            ]);

            return response()->json([
                'message' => $requisitionAddLines['message'],
                'error' => $requisitionAddLines['error'] ?? 'Unknown error'
            ], 500);

        } catch (\Throwable $e) {
            Log::error('Exception occurred while creating requisitionLines.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create requisitionLines ##',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
//        dd($id);
        $this->authorize('view', Requisitions::query()->findOrFail($id));
        try {
            $details = $this->service->getRequisitionRelatedItems($id);
            return view('procurement.requisitionItems.create', compact('details'));
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
