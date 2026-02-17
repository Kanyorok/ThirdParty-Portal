<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentActionsController extends Controller
{
    public function preview(Request $request, Document $document): View
    {
        $this->authorize('view', $document);

        $view = $request->ajax() ? 'dms.files.preview' : 'dms.files.preview-page';

        return view($view)->with('file', $document)->with('service', new DocumentService($document));
    }
}
