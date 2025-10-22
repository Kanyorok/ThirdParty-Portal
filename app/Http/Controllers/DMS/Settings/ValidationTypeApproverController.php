<?php

namespace App\Http\Controllers\DMS\Settings;

use App\Http\Controllers\Controller;
use App\Models\Core\SpecialPermission;
use App\Models\DMS\DocumentValidationType;
use App\Traits\Controller\SpecialPermissionTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationTypeApproverController extends Controller
{
    use SpecialPermissionTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(DocumentValidationType $documentValidationType): JsonResponse
    {
        $this->authorize('view', $documentValidationType);
        return $this->permissions($documentValidationType->permissions(), true);
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

    protected function _trashRoute(SpecialPermission $permission): string
    {
        return route('doc-validation-type-approvers.destroy', [$permission->model->ValidationTypeId, $permission->Id]);
    }
}
