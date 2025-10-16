<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ThirdPartyDocumentsController extends Controller
{
    /**
     * Upload one or multiple supporting documents and attach them to a third party
     */
    public function store(Request $request, int $thirdPartyId): JsonResponse
    {
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|max:20480', // 20MB per file; adjust as needed
        ]);

        $actor = $request->user();

        // Authorization: ensure user is allowed to update their own third party
        $thirdParty = ThirdParties::findOrFail($thirdPartyId);
        if (method_exists($actor, 'ThirdPartyId') || isset($actor->ThirdPartyId)) {
            if ((int)($actor->ThirdPartyId ?? 0) !== (int)$thirdParty->Id) {
                return response()->json(['message' => 'Unauthorized to upload for this third party'], 403);
            }
        }

        $files = $request->file('files');
        if (!is_array($files)) {
            $files = [$files];
        }

        $attached = [];
        foreach ($files as $file) {
            if (!$file) { continue; }
            // Use DocumentsTrait via DocumentService to create internal document and relate to model
            $doc = $thirdParty->newDocument(ModulesEnum::ThirdParty, $file, [], $actor);
            $attached[] = [
                'id' => $doc->Id,
                'name' => $doc->Name,
                'mimeType' => $doc->MimeType,
                'size' => $doc->current?->Size,
                'version' => $doc->current?->Version,
                'createdOn' => optional($doc->CreatedOn)->toIso8601String(),
                'previewUrl' => url('/dms/document/' . $doc->Id . '/preview'),
            ];
        }

        return response()->json([
            'message' => 'Documents uploaded successfully',
            'documents' => $attached,
        ], 201);
    }
}
