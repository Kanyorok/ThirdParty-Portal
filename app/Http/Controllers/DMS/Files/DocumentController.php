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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Repository $repository): FilesCollection
    {
        return new FilesCollection($repository->documents()->whereHas('current')->with(['current'])->latest('t_Documents.Id')->paginate(50));
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
    public function show(Repository $repository, Document $document)
    {
        $document->load(['current', 'repository', 'creator', 'category', 'properties'])->withCount('versions');
        return view('dms.files.show')
            ->with('repoService', new RepositoryService($repository))
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
    public function destroy(Document $document)
    {
        //
    }
}
