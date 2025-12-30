<?php

namespace App\Http\Controllers\DMS\Files;

use App\Enums\Core\ExtensionsEnum;
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
        $allowedFiles = '<p class="my-2">Allowed File Types for Upload:';

        foreach (ExtensionsEnum::getAllowedExtensionsForUpload() as $item) {
            $allowedFiles .= "<b>{$item['description']}</b> : {$item['extensions']} . <span class='mx-2'></span>";
        }
        $allowedFiles .= '</p>';
        $root = RepositoryService::root();
        return view('dms.files.create')
            ->with('repositories', RepositoryService::getUser($request->user(), [$root->Id, null]))
            ->with('allowedFiles', $allowedFiles)
            ->with('root', $root);
    }
}
