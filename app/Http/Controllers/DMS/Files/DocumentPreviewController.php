<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use Illuminate\Http\Request;

class DocumentPreviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Document $document)
    {
        $this->authorize('view', $document);
        return view('dms.files.embed')->with('file', $document)->with('service', new DocumentService($document));
    }
}
