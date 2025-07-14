<?php

namespace App\Http\Controllers\DMS\Repo;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\RepositoryRequest;
use App\Http\Resources\DMS\RepositoryCollection;
use App\Http\Resources\DMS\RepositoryResource;
use App\Models\DMS\Repository;
use App\Services\DMS\RepositoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class RepositoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|RepositoryCollection
    {
        return $this->show($request, RepositoryService::root());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Repository $repository): View|RepositoryCollection
    {
        if ($request->ajax()) {
            return new RepositoryCollection($repository->repositories()->user($request->user())->withCount('documents')->paginate(20));
        }
        return view('dms.repo.show')
            ->with('repository', $repository)->with('service', new RepositoryService($repository));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RepositoryRequest $request): JsonResponse
    {
        $repository = $request->getParentRepo();
        try {
            return DB::transaction(function () use ($request, $repository) {
                return $this->succeeded('repository created successfully', data: [
                    'data' => new RepositoryResource(
                        RepositoryService::create($repository, $request->string('repository_name')->trim()->toString(), $request->user(), $request->string('repository_description', '')->trim()->toString())->repo
                    )
                ]);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error DMS repository create : ');
            Log::error($e);
            return $this->errored('unexpected error, try again later');
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
     * Permissions
     */
    public function edit(Repository $repository)
    {
        return view('dms.repo.summary')
            ->with('repository', $repository)->with('service', new RepositoryService($repository));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Repository $repository): JsonResponse
    {
        $actor = $request->user();
        try {
            return DB::transaction(function () use ($repository, $actor, $request) {
                $repository->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $request->user()->Id,
                ])->save();

                DB::table('t_Documents')->where('RepositoryId', $repository->Id)->update([
                    'DeletedOn' => now(),
                    'DeletedBy' => $request->user()->Id,
                ]);

                DB::table('t_Repositories')->where('ParentId', $repository->Id)->update([
                    'DeletedOn' => now(),
                    'DeletedBy' => $request->user()->Id,
                ]);

                activity()->causedBy($actor)->performedOn($repository)->event('delete')->log('trashed repository  ' . $repository->Name . '.');

                return $this->succeeded('repository trashed', data: [
                    'data' => new RepositoryResource($repository)
                ]);
            });
        } catch (Throwable|Exception $e) {
            Log::error('trash board member : ' . $e);
            return $this->errored('an unexpected error occurred');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RepositoryRequest $request, Repository $repository): JsonResponse
    {
        try {
            return $this->succeeded('repository updated successfully', data: [
                'data' => new RepositoryResource(
                    (new RepositoryService($repository))->update($request->string('repository_name')->trim()->toString(), $request->user(), $request->string('repository_description', '')->trim()->toString())
                        ->repo->loadCount('documents')
                )
            ]);
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error DMS repository update : ');
            Log::error($e);
            return $this->errored('unexpected error, try again later');
        }
    }
}
