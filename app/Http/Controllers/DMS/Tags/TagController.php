<?php

namespace App\Http\Controllers\DMS\Tags;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\NewTagRequest;
use App\Models\DMS\DMSTags;
use App\Models\DMS\DocumentTags;
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
    public function show(DMSTags $dMSTags): View
    {
        return view('dms.tags.show', [
            'tag' => $dMSTags,
            'documents_count' => DocumentTags::where('TagId', $dMSTags->Id)->count(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NewTagRequest $request, DMSTags $dMSTags): JsonResponse
    {
        $actor = $request->user();
        $visibility = $request->getVisibility();

        try {
            return DB::transaction(function () use ($request, $dMSTags, $actor, $visibility) {
                $dMSTags->update([
                    'Name' => $request->string('Name')->toString(),
                    'Description' => $request->string('Description', '')->toString(),
                    'Visibility' => $visibility->value,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($dMSTags)->event('update')->log('updated ' . $dMSTags->Name . ' document tag.');

                return $this->succeeded('tag updated successfully', route('file-tags.show', [$dMSTags->TagID]));
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error updating Document Tag: ');
            Log::error($e);

            return $this->errored('updating tag failed, try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, DMSTags $dMSTags): JsonResponse
    {
        $actor = $request->user();

        try {
            return DB::transaction(function () use ($dMSTags, $actor) {
                $dMSTags->forceFill([
                    'DeletedBy' => $actor->Id,
                    'DeletedOn' => now(),
                ])->save();

                activity()->causedBy($actor)->performedOn($dMSTags)->event('delete')->log('deleted document tag: ' . $dMSTags->Name . '.');

                return $this->succeeded('tag deleted successfully', route('file-tags.index'));
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error updating Document Tag: ');
            Log::error($e);

            return $this->errored('deleting tag failed, try again later');
        }
    }
}
