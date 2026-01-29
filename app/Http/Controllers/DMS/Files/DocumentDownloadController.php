<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentDownloadController extends Controller
{
    /**
     * Actual Download
     */
    public function index(Request $request, Document $document)
    {
        $this->authorize('view', $document);
        $token = $request->str('token', '')->trim()->toString();
        $service = new DocumentService($document);
        abort_unless(((! empty($token)) && $service->validateToken($request->user(), $token)), 401);

        return (Response($service->getFileContent(false), 200))
            ->header('ContentType', $service->type->getMimeType())
            ->header('Content-Disposition', 'attachment; filename=' . $document->Name);
    }

    /**
     * Generate Download Route
     */
    public function store(Request $request, Document $document): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $document);
        activity()->causedBy($request->user())->performedOn($document)->event('download')->log('downloaded file ' . $document->Name);
        if ($request->has('fetch_link')) {
            return $this->succeeded('file downloaded successfully', route: route('file-download.index', ['document' => $document->DocumentId, 'token' => (new DocumentService($document))->generateToken($request->user())]));
        }

        return redirect()->route('file-download.index', ['document' => $document->DocumentId, 'token' => (new DocumentService($document))->generateToken($request->user())]);
    }
}
