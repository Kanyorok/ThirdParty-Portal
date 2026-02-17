<?php

namespace App\Http\Controllers\DMS\API;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use Illuminate\Http\Request;

class DocumentPreviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $documentId = $request->get('documentId');
        $document = Document::query()->where(function ($query) use ($documentId) {
            $query->where('DocumentId', $documentId)->orWhere('Id', $documentId);
        })->first();
        if (! $document instanceof Document) {
            abort(404, 'Document not found');
        }

        return view('dms.files.preview')->with('file', $document)->with('service', new DocumentService($document));
    }
}
