<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Services\ERP\RequisitionItemService;
use Illuminate\Http\Request;
use App\Http\Requests\ERP\Requisition\RequisitionItemRequest;

class RequisitionItems extends Controller
{

    public function __construct(protected RequisitionItemService $service) {

        $this->middleware('ajax')->except(['index', 'show']);
        $this->authorizeResource(RequisitionLines::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view ('procurement.requisitionItems.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view ('procurement.requisitionItems.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequisitionItemRequest $request)
    {
        //
        $requisitionItem = DB::transaction(static function () use ($request) {
            return $this->service->create($request->validated());
        });
        return response()->json([
            'message' => 'Requisition line saved successfully.',
            'data' => $requisitionItem,
        ], 201);
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
