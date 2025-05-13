<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\PurchaseOrderRequest;
use App\Models\Procurement\Order;
use App\Models\Procurement\RequisitionLines;
use App\Services\Orders\OrderService;
use App\Services\Procurement\Items\ItemService;
use App\Services\ThirdParty\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(protected ItemService $itemService, protected SupplierService $supplierService, protected OrderService $orderService)
    {

        $this->middleware('ajax')->except(['index', 'create']);
        $this->authorizeResource(Order::class);
    }

    public function getItemDetails($item): JsonResponse
    {
        try{
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

    public function getSupplierDetails($supplier): JsonResponse
    {
        try{
            $details = $this->supplierService->getSupplierDetails($supplier);
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

    public function getSuppliers(): JsonResponse
    {
        try{
            $details = $this->supplierService->getSuppliers();
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
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view("procurement.orders.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view("procurement.orders.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseOrderRequest $request):JsonResponse
    {
        //
        try {
            $validatedData = $request->validated();

            $actor = $request->user();
            if (!$actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $POAdd = $this->orderService->addPO(
                $actor,
                $validatedData['supplier'],
                $validatedData['poDate'],
                $validatedData['rfqNo'],
                $validatedData['priority'],
                $validatedData['terms']
            );


            $POLinesAdd = $this->orderService->addPOLines(
                $actor,
                $validatedData['itemCode'],
                $validatedData['quantity'],
                $validatedData['unitPrice'],
                $validatedData['tax'],
                $validatedData['discount'],
                $validatedData['lineTotal']

            );


            if ($POAdd['status'] === 'success' || $POLinesAdd['status'] === 'success') {
                return response()->json([
                    'message' => $POAdd['message'],
                    'route' =>route('order.create')
                ], 200);
            }

            // Log failure with details
            \Log::error('Failed to create order.', [
                'input' => $validatedData,
                'user_id' => $actor->id ?? null,
                'service_response' => $POAdd,
            ]);

            return response()->json([
                'message' => $POAdd['message'],
                'error' => $POAdd['error'] ?? 'Unknown error'
            ], 500);



        } catch (\Throwable $e) {
            \Log::error('Exception occurred while creating order.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create order',
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
