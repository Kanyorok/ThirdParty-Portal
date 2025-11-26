<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use App\Traits\Controller\DocumentsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TrashDocumentController extends Controller
{
    use DocumentsTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except('index');
    }

    /**
     * All Trashed
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->expectsJson()) {
            return $this->documents(Document::onlyTrashed(), $request->user(), ['repository', 'current', 'deleter:Id,Name']);
        }

        return view('dms.files.trash');
    }

    /**
     * Restore Selected.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Restore file
     */
    public function update(Request $request, string $documentId)
    {
        $actor = $request->user();
        $document = Document::onlyTrashed()->where('DocumentId', $documentId)->user($actor)->first();
        if (!$document instanceof Document) {
            return $this->errored('file not found');
        }

        try {
            return DB::transaction(function () use ($document, $actor) {
                (new DocumentService($document))->restore($actor);
                return $this->succeeded('file restored successfully.');
            });
        } catch (\Exception|\Throwable $e) {
            Log::error("Error restoring document {$document->Id} : ");
            Log::error($e);
        }
        return $this->errored('an unexpected error occurred, try again later');
    }

    /**
     * delete Forever.
     */
    public function destroy(string $id)
    {
        //
    }
}
