<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Models\DMS\LegalHold;
use App\Traits\Controller\LegalHoldTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentLegalHoldController extends Controller
{
    use LegalHoldTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     * @throws Exception
     */
    public function __invoke(Request $request, Document $document): JsonResponse
    {
        $this->authorize('viewAny', LegalHold::class);

        return $this->getLegalHolds(query: $document->holds(), counts: ['documents']);
    }
}
