<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use Illuminate\View\View;

class DocumentActionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    public function preview(Document $document): View
    {
        return view('dms.files.preview')->with('file', $document)->with('service', new DocumentService($document));
    }
}
