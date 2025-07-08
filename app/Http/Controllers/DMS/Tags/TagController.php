<?php

namespace App\Http\Controllers\DMS\Tags;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\NewTagRequest;
use App\Models\DMS\DMSTags;
use App\Traits\Controller\DMSTagTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class TagController extends Controller
{
    use DMSTagTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
        //$this->authorizeResource(DMSTags::class);
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
        return view('dms.tags.show', ['tag' => $dMSTags->loadCount('documents')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NewTagRequest $request, string $tagId): JsonResponse
    {
        $tag = DMSTags::query()->where('TagID', $tagId)->first();
        if (!$tag instanceof DMSTags) {
            return $this->errored('invalid tag given, try again later');
        }
        $this->authorize('update', $tag);

        $actor = $request->user();
        $visibility = $request->getVisibility();
        try {
            return DB::transaction(function () use ($request, $tag, $actor, $visibility) {
                $tag->update([
                    'Name' => $request->string('Name')->toString(),
                    'Description' => $request->string('Description', '')->toString(),
                    'Visibility' => $visibility->value,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($tag)->event('update')->log('updated ' . $tag->Name . ' document tag.');
                return $this->succeeded('tag updated successfully', route('file-tags.show', [$tag->TagID]));
            });
        } catch (Throwable|Exception $e) {
            Log::error('Error updating Document Tag: ');
            Log::error($e);
            return $this->errored('updating tag failed, try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $tagId): JsonResponse
    {
        $tag = DMSTags::query()->where('TagID', $tagId)->first();
        if (!$tag instanceof DMSTags) {
            return $this->errored('invalid tag given, try again later');
        }
        $this->authorize('delete', $tag);
        $actor = $request->user();
        try {
            return DB::transaction(function () use ($tag, $actor) {
                $tag->forceFill([
                    'DeletedBy' => $actor->Id,
                    'DeletedOn' => now(),
                ])->save();

                activity()->causedBy($actor)->performedOn($tag)->event('delete')->log('deleted document tag: ' . $tag->Name . '.');
                return $this->succeeded('tag deleted successfully', route('file-tags.index'));
            });
        } catch (Throwable|Exception $e) {
            Log::error('Error updating Document Tag: ');
            Log::error($e);
            return $this->errored('deleting tag failed, try again later');
        }
    }
}
