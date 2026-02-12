<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\DocumentValidationRequest;
use App\Http\Resources\DMS\FileResource;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentValidation;
use App\Models\DMS\DocumentValidationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DocumentValidationController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Document $document): JsonResponse|View
    {
        $this->authorize('update', $document);
        if (!$document->ext()?->canSign()){
            return $this->errored('Document cannot be signed');
        }

        return view('dms.files.validation')
            ->with('file',$document)->with('types', DocumentValidationType::get([ "ValidationTypeId", "Name"]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentValidationRequest $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $actor = $request->user();
        $type = $request->getValidationType();
        $validationId = $request->createValidationId($type);

        try {
            return DB::transaction(function () use ($actor, $document, $type, $validationId) {
                $validation = DocumentValidation::create([
                    'Name' => 'Validate '.$document->Name,
                    'ValidationId' => $validationId,
                    'DocumentId' => $document->Id,
                    'ValidationTypeId' => $type->Id,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($validation)->event('create')->log('submitted document for validation : ' . $validation->ValidationId);

                return $this->succeeded('validation submitted successfully');
            });
        } catch (\Throwable $e) {
            Log::error('Could not move document ' . $e);
        }

        return $this->errored('an unexpected error occurred, try again later');
    }
}
