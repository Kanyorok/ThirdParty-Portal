<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\PurchaseOrderRequest;
use App\Models\Procurement\Order;
use App\Models\Procurement\RequisitionLines;
use App\Models\Procurement\Requisitions;
use App\Services\Orders\OrderService;
use App\Services\Procurement\Items\ItemService;
use App\Services\ThirdParty\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function __construct(protected ItemService $itemService, protected SupplierService $supplierService, protected OrderService $orderService)
    {

        $this->middleware('ajax')->except(['index', 'create','show']);
//        $this->authorizeResource(Order::class);
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

        \Log::info('getSuppliers() was triggered.');


        try{
            $suppliers = $this->supplierService->getSuppliers();
            \Log::info('Suppliers data:', $suppliers->toArray());
            return response()->json([
                'success' => true,
                'data' => $suppliers,
            ]);}
        catch(\Exception $e){
            \Log::error('Error fetching suppliers: ' . $e->getMessage());

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

        try {
            $details = $this->orderService->fetchOrders();
            // if ($details ) {
            return view('procurement.orders.index', compact('details'));
            // /}
            // else{  return view('procurement.requisitions.create', ['details' => []]);
            // }
        } catch (\Exception $e) {
            Log::error('Create page failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
//        return view("procurement.orders.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        try {
            $suppliers = $this->supplierService->getSuppliers();
            \Log::info('Suppliers loaded in create():', $suppliers->toArray());
        } catch (\Exception $e) {
            \Log::error('Error fetching suppliers in create(): ' . $e->getMessage());
            $suppliers = collect(); // fallback to empty collection
        }
        //


        return view("procurement.orders.create", compact('suppliers'));
//        return view("procurement.orders.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseOrderRequest $request): JsonResponse
    {

//       dd($request->all());
        try {
            $validatedData = $request->validated();

            $actor = $request->user();
            if (!$actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $POAdd = $this->orderService->addPO(
                $validatedData['supplier'],
                $validatedData['pODate'],
                $validatedData['refNo'],
                $validatedData['priority'],
                $validatedData['terms'],
                $actor
            );

            if ($POAdd['status'] !== 'success') {
                \Log::error('Failed to create PO.', [
                    'input' => $validatedData,
                    'user_id' => $actor->id ?? null,
                    'service_response' => $POAdd,
                ]);

                return response()->json([
                    'message' => $POAdd['message'] ?? 'Failed to create purchase order',
                    'error' => $POAdd['error'] ?? 'Unknown error'
                ], 500);
            }

            $poId = $POAdd['po_id'] ?? null;

            if (!$poId) {
                \Log::error('PO created but no ID returned.', [
                    'response' => $POAdd
                ]);

                return response()->json([
                    'message' => 'Purchase order created but no ID returned.',
                    'error' => 'Missing PO ID'
                ], 500);
            }

            // Process each PO line
            foreach ($validatedData['itemCode'] as $index => $itemCode) {
                $POLinesAdd = $this->orderService->addPOLines(
                    $itemCode,
                    $validatedData['quantity'][$index],
                    $validatedData['unitPrice'][$index],
                    $validatedData['tax'][$index],
                    $validatedData['discount'][$index],
                    $validatedData['lineTotal'][$index],
                    $actor,
                    $poId
                );

                if ($POLinesAdd['status'] !== 'success') {
                    \Log::error('Failed to add PO line.', [
                        'index' => $index,
                        'item' => $itemCode,
                        'response' => $POLinesAdd,
                    ]);

                    return response()->json([
                        'message' => 'Failed to add PO line',
                        'error' => $POLinesAdd['error'] ?? 'Line creation error'
                    ], 500);
                }

            }

            $POSum = $this->orderService->AddPurchaseOrderSum(
                $poId
            );
            if ($POSum['status'] !== 'success') {
                \Log::error('Failed to calculate POs sum.', [
                    'po_id' => $poId,
                    'response' => $POSum,
                ]);

                return response()->json([
                    'message' => 'Failed to calculate PO sum',
                    'error' => $POSum['error'] ?? 'Sum calculation error'
                ], 500);
            }



            // Everything succeeded
            return response()->json([
                'message' => $POAdd['message'] ?? 'Order created successfully',
                'route' => route('purchaseOrder.index')
            ], 200);

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
//        $this->authorize('view', Order::query()->findOrFail($id));
//        return view('procurement.orders.show');

//        dd($id);

        try {
            $order = Order::findOrFail($id); // This will throw 404 if not found
            $this->authorize('view', $order); // Authorize the order object itself

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            $lineInfo = $this->orderService->fetchOrderLineDetails($id);

            return view('procurement.orders.show', compact('orderInfo', 'lineInfo'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access attempt to view Order ID: {$id} by user ID: " . auth()->id());
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order ID {$id} not found. Exception: " . $e->getMessage());
            return redirect()->back()->with('error', 'Order not found.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch order ID {$id}. Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to fetch order.');
        }

    }

    public function relatedPO()
    {
//        $this->authorize('view', Order::query()->findOrFail($id));
//        dd($id);
        return view('procurement.orders.index');

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

    public function linkRFQ(){
        Log::info('linkRFQ() was called');
//        return view('procurement.orders.rfqLInk');
    }


    public function fetchRFQ(){
        return view('procurement.orders.rfqLInk');
    }
}
