<?php

namespace App\Http\Controllers\DMS\Tags;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\NewTagRequest;
use App\Models\DMS\DMSTags;
use App\Traits\Controller\DMSTagTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    use DMSTagTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except('index');
        $this->authorizeResource(DMSTags::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dt(DMSTags::query(), $request->user());
        }

        return view('dms.tags.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('dms.tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewTagRequest $request): JsonResponse
    {
        $actor = $request->user();

        try {
            $this->new($request->string('Name')->toString(), $request->string('Description', '')->toString(), $actor, $request->getVisibility(), $request->getDocument());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('tag added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(DMSTags $dMSTags)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DMSTags $dMSTags)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DMSTags $dMSTags)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DMSTags $dMSTags)
    {
        //
    }
}
