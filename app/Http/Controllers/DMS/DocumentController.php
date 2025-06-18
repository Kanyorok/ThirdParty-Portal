<?php

namespace App\Http\Controllers\DMS;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\UploadDocumentRequest;
use App\Http\Resources\DMS\FileResource;
use App\Models\DMS\Document;
use App\Models\DMS\Repository;
use App\Services\DMS\DocumentService;
use App\Services\DMS\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(UploadDocumentRequest $request, Repository $repository): \Illuminate\Http\JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $repository) {
                $document = DocumentService::createUpload($repository, $request->file('file'), $request->user())->document;
                return $this->succeeded('document uploaded successfully', data: [
                    'data' => new FileResource($document)
                ]);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Exception|\Throwable $e) {
            Log::error('Error DMS upload document : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        //
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
