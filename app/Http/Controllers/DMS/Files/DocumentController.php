<?php

namespace App\Http\Controllers\DMS\Files;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\UploadDocumentRequest;
use App\Http\Resources\DMS\FileResource;
use App\Http\Resources\DMS\FilesCollection;
use App\Models\DMS\Document;
use App\Models\DMS\Repository;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DocumentController extends Controller
{
    /* public function __construct()
     {
         $this->middleware('ajax')->except('show');
         $this->authorizeResource(Document::class);
     }*/

    /**
     * Display a listing of the resource.
     */
    public function index(Repository $repository): FilesCollection
    {
        return new FilesCollection($repository->documents()->user(auth()->user())->whereHas('current')->with(['current'])->latest('t_Documents.ModifiedOn')->paginate(50));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UploadDocumentRequest $request, Repository $repository): JsonResponse
    {
        try {
            return $this->succeeded('document uploaded successfully', data: [
                'data' => new FileResource(DocumentService::createUpload($repository, $request->file('file'), $request->user())->document)
            ]);
        } catch (ErroredException $e) {
            return $e->toJson();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Repository $repository, Document $document): View
    {
        $actor = $request->user();
        $document->load(['current', 'repository', 'creator', 'category', 'properties'])->withCount('versions');

        $lock = Cache::lock('view-document-' . $document->DocumentId, 100);
        if ($lock->get()) {
            activity()->causedBy($actor)->performedOn($document)->event('view')->log('viewed document  ' . $document->Name . '.');
        }

        return view('dms.files.show')->with('repoService', new RepositoryService($repository))
            ->with('tags', (new DocumentService($document))->tags($actor)->get())
            ->with('file', $document);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repository $repository, Document $document)
    {
        if ($document->RepositoryId !== $repository->Id) {
            return $this->errored('file not found');
        }

        return view('dms.files.summary')
            ->with('file', $document)->with('service', new DocumentService($document));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Repository $repository, Document $document)
    {
        if ($document->RepositoryId !== $repository->Id) {
            return $this->errored('file not found');
        }

        try {
            return DB::transaction(function () use ($document) {
                $document->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => auth()->user()->Id,
                ])->save();

                activity()->causedBy(auth()->user())->performedOn($document)->event('delete')->log('trashed document  ' . $document->Name . '.');
                return $this->succeeded('document trashed successfully', data: [
                    'data' => new FileResource($document)
                ]);
            });
        } catch (Throwable|Exception $e) {
            Log::error('deleting file failed :');
            Log::error($e);
        }
        return $this->errored('an unexpected error occurred');
    }
}
