<?php

namespace App\Http\Controllers\DMS\LegalHold;

use App\Http\Controllers\Controller;
use App\Models\DMS\DocumentLegalHold;
use App\Models\DMS\LegalHold;
use App\Traits\Controller\DocumentsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentLegalHoldController extends Controller
{
    use DocumentsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $legalHoldId): JsonResponse
    {
        $legalHold = LegalHold::query()->where('t_DMSLegalHolds.Ref', $legalHoldId)->first();
        if (! $legalHold instanceof LegalHold) {
            return $this->errored('Invalid legal hold provided');
        }
        $this->authorize('view', $legalHold);

        return $this->documents($legalHold->documents(), $request->user());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentLegalHold $documentLegalHold)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentLegalHold $documentLegalHold)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentLegalHold $documentLegalHold)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentLegalHold $documentLegalHold)
    {
    }
}
