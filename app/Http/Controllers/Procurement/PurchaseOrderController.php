<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RequisitionLines;
use App\Services\Procurement\Items\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(protected ItemService $itemService)
    {

        $this->middleware('ajax')->except(['index', 'create']);
        $this->authorizeResource(RequisitionLines::class);
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view("procurement.purchaseOrders.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view("procurement.purchaseOrders.create");
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
}
