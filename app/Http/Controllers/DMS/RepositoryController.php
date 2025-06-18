<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\DMS\FilesCollection;
use App\Models\DMS\Repository;
use App\Services\DMS\RepositoryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dms.repo.show')->with('repository', RepositoryService::root());
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
    public function show(Request $request, Repository $repository)
    {
        if ($request->ajax()) {
            return new FilesCollection($repository->documents()->whereHas('current')->with(['current'])->latest('t_Documents.Id')->paginate(50));
        }
        return view('dms.repo.show')->with('repository', $repository);
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
