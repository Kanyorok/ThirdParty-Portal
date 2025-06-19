<?php

namespace App\Http\Controllers\DMS;

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
    public function index(Request $request)
    {
        return $this->show($request, RepositoryService::root());
        //return view('dms.repo.show')->with('repository', );
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
     * Display the specified resource.
     */
    public function show(Request $request, Repository $repository): View|RepositoryCollection
    {
        if ($request->ajax()) {
            return new RepositoryCollection($repository->repositories()->withCount('documents')->paginate(20));
        }
        return view('dms.repo.show')
            ->with('repository', $repository)->with('service', new RepositoryService($repository));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repository $repository)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Repository $repository)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Repository $repository)
    {
        //
    }
}
