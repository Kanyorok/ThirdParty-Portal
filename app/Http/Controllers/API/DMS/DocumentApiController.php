<?php

namespace App\Http\Controllers\API\DMS;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Enums\Core\VisibilityEnum;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $actor = $request->user();
            $q = trim((string) $request->query('q', ''));
            $page = max(1, (int) $request->query('page', 1));
            $limit = max(1, min(50, (int) $request->query('limit', 20)));

            $query = Document::query()
                ->whereHas('current')
                ->with(['current', 'repository'])
                ->orderByDesc('ModifiedOn');

            $onlyMine = $request->boolean('my');
            // ERP users: optionally filter to only their own documents
            if ($actor instanceof ThirdPartyUser) {
                // Third-party users: always restrict to Public, and if my=1 then also CreatedBy = actor id
                $query->where('t_Documents.Visibility', VisibilityEnum::Public->value);
                if ($onlyMine) {
                    $query->where('t_Documents.CreatedBy', $actor->Id);
                }
            } else {
                // Internal users: apply permission scope first
                $query->user($actor);
                if ($onlyMine) {
                    $query->where('t_Documents.CreatedBy', $actor->Id);
                }
            }

            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('t_Documents.Name', 'like', "%{$q}%")
                        ->orWhereHas('current', function ($cw) use ($q) {
                            $cw->where('t_DocumentVersions.Name', 'like', "%{$q}%");
                        });
                });
            }

            $total = (clone $query)->count();
            $items = $query->forPage($page, $limit)->get();

            $data = $items->map(function (Document $doc) {
                return [
                    'id' => $doc->Id,
                    'name' => $doc->Name,
                    'repository' => $doc->repository?->Name,
                    'visibility' => $doc->Visibility?->value,
                    'size' => $doc->current?->Size,
                    'version' => $doc->current?->Version,
                    'mimeType' => $doc->ext()?->getMimeType(),
                    'createdOn' => optional($doc->CreatedOn)?->toIso8601String(),
                    'modifiedOn' => optional($doc->ModifiedOn)?->toIso8601String(),
                    // Build a predictable path without requiring route registration
                    'previewUrl' => url('/dms/document/' . $doc->Id . '/preview'),
                ];
            });

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to list documents',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}


