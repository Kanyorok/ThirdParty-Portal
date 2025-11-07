<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;

class DocumentUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $root = RepositoryService::root();
        return view('dms.files.create')
            ->with('repositories', RepositoryService::getUser($request->user(), [$root->Id, null]))
            ->with('root', $root);
    }
}
