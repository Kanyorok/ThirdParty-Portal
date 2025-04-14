<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ERP\RequisitionLines;
use App\Services\ERP\RequisitionItemService;
use Illuminate\Http\Request;
use App\Http\Requests\ERP\Requisition\RequisitionItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class RequisitionItemsController extends Controller
{

    public function __construct(protected RequisitionItemService $service) {

        $this->middleware('ajax')->except(['index', 'show','create']);
        $this->authorizeResource(RequisitionLines::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view ('procurement.requisitionItems.create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $requisitionItem = RequisitionLines::all();
        //

        return view ('procurement.requisitionItems.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequisitionItemRequest $request):JsonResponse
    {
        try{
        $actor = $request->user();
        $service = $this->service;
        $requisitionItem = DB::transaction(static function () use ($service, $request,$actor) {
            return $service->create($request->validated(),$actor);
        });
        return response()->json([
            'message' => 'Requisition line saved successfully.',
            'data' => $requisitionItem,
        ], 201);
    }
        catch (\Throwable $e) {
            // Log the error for debugging
            \Log::error('RequisitionItem store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to save requisition line.',
                'error' => $e->getMessage(),
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
