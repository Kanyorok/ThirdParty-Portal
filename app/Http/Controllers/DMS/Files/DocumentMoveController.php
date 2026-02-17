<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\MoveRequest;
use App\Http\Resources\DMS\FileResource;
use App\Models\DMS\Document;
use App\Services\DMS\RepositoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DocumentMoveController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function index(Request $request, Document $document): View
    {
        $this->authorize('update', $document);

        return view('dms.files.move')
            ->with('file', $document)
            ->with('parent', $document->repository)
            ->with('repositories', RepositoryService::getUserQuery($request->user())->where('t_Repositories.Id', '!=', $document->RepositoryId)->with(['parent:Id,Name'])->get(['Id', 'RepositoryId', 'Name', 'Visibility', 'ParentId']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MoveRequest $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $actor = $request->user();
        $repo = $request->getRepository($actor);

        try {
            return DB::transaction(function () use ($actor, $document, $repo) {
                $document->update([
                    'RepositoryId' => $repo->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($document)->event('Change Repository')->log('File moved to repository ' . $repo->Name . '.');

                return $this->succeeded('file moved to repository ' . $repo->Name . ' successfully', data: [
                    'data' => (new FileResource($document))->setMinified(true),
                ]);
            });
        } catch (Throwable | Exception $e) {
            Log::error('Could not move document ' . $e);
        }

        return $this->errored('an unexpected error occurred, try again later');
    }
}
