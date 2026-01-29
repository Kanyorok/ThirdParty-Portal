<?php

namespace App\Http\Controllers\DMS\Files;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\SharePartyRequest;
use App\Http\Requests\Core\VisibilityRequest;
use App\Http\Resources\DMS\FileResource;
use App\Models\Core\SpecialPermission;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use App\Traits\Controller\SpecialPermissionTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentPermissionController extends Controller
{
    use SpecialPermissionTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return $this->permissions($document->permissions(), $request->user()->can('delete', $document));
    }

    public function visibility(VisibilityRequest $request, Document $document): JsonResponse
    {
        $this->authorize('share', $document);

        try {
            return $this->succeeded('file updated successfully', data: [
                'data' => new FileResource(
                    (new DocumentService($document))->visibility($request->getVisibility(), $request->user())->document
                ),
            ]);
        } catch (ErroredException $e) {
            return $e->toJson();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SharePartyRequest $request, Document $document): JsonResponse
    {
        $this->authorize('share', $document);
        $assignee = $request->getParty();
        $permission = $request->getRole();

        try {
            return $this->succeeded('file permissions updated', data: [
                'data' => new FileResource(
                    (new DocumentService($document))->addPermission($assignee, $permission, $request->user())->document
                ),
            ]);
        } catch (ErroredException $e) {
            return $e->toJson();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Document $document, int $permission_id): JsonResponse
    {
        $this->authorize('share', $document);
        $specialPermission = $document->permissions()->where('Id', $permission_id)->first();
        if (! $specialPermission instanceof SpecialPermission) {
            return $this->errored('permission not found');
        }

        try {
            return $this->succeeded('file permissions updated', data: [
                'data' => new FileResource(
                    (new DocumentService($document))->removePermission($specialPermission, $request->user())->document
                ),
            ]);
        } catch (ErroredException $e) {
            return $e->toJson();
        }
    }

    protected function _trashRoute(SpecialPermission $permission): string
    {
        return route('file-permissions.destroy', [$permission->model->DocumentId, $permission->Id]);
    }
}
