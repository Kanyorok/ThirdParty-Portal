<?php

namespace App\Http\Controllers\DMS\Repo;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Core\VisibilityRequest;
use App\Http\Resources\DMS\RepositoryResource;
use App\Models\Core\SpecialPermission;
use App\Models\DMS\Repository;
use App\Services\DMS\RepositoryService;
use App\Traits\Controller\SpecialPermissionTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepositoryPermissionController extends Controller
{
    use SpecialPermissionTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Repository $repository): JsonResponse
    {
        $this->authorize('view', $repository);
        return $this->permissions($repository->permissions(), $request->user()->can('delete', $repository));
    }

    public function visibility(VisibilityRequest $request, Repository $repository): JsonResponse
    {
        $this->authorize('update', $repository);
        try {
            return $this->succeeded('repository updated successfully', data: [
                'data' => new RepositoryResource(
                    (new RepositoryService($repository))->visibility($request->getVisibility(), $request->user())->repo
                )
            ]);
        } catch (ErroredException $e) {
            return $e->toJson();
        }
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
    public function show(SpecialPermission $specialPermission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SpecialPermission $specialPermission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpecialPermission $specialPermission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpecialPermission $specialPermission)
    {
        //
    }

    protected function _trashRoute(SpecialPermission $permission): string
    {
        return route('repo-permissions.destroy', [$permission->model->RepositoryId, $permission->Id]);
    }
}
