<?php

namespace App\Http\Controllers\DMS\Repo;

use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\MoveRequest;
use App\Http\Resources\DMS\RepositoryResource;
use App\Models\DMS\Repository;
use App\Services\DMS\RepositoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RepositoryMoveController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function index(Request $request, Repository $repository): View
    {
        $this->authorize('update', $repository);

        return view('dms.files.move')
            ->with('file', $repository)
            ->with('parent', $repository->parent)
            ->with('repositories', RepositoryService::getUserQuery($request->user())->whereNotIn('t_Repositories.Id', [$repository->ParentId, $repository->Id])->get(['RepositoryId', 'Name', 'Visibility']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MoveRequest $request, Repository $repository): JsonResponse
    {
        $this->authorize('update', $repository);

        $actor = $request->user();
        $repo = $request->getRepository($actor);
        if ($repo->Id === $repository->ParentId) {
            throw ValidationException::withMessages([
                'repository_id' => 'repository cannot be moved to itself',
            ]);
        }

        try {
            return DB::transaction(function () use ($actor, $repository, $repo) {
                $repository->update([
                    'ParentId' => $repo->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($repository)->event('Change Repository')->log('File moved to repository ' . $repo->Name . '.');

                return $this->succeeded('repository moved to repository ' . $repo->Name . ' successfully', data: [
                    'data' => (new RepositoryResource($repository))->setMinified(true),
                ]);
            });
        } catch (Throwable | Exception $e) {
            Log::error('Could not move repository ' . $e);
        }

        return $this->errored('an unexpected error occurred, try again later');
    }
}
