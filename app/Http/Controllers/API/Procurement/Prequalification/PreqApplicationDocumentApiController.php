<?php

namespace App\Http\Controllers\API\Procurement\Prequalification;

use App\Enums\Core\ModulesEnum;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StoreApplicationDocumentRequest;
use App\Models\DMS\Document;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationApplicationDocument;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use App\Services\ThirdParty\PortalDocumentPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreqApplicationDocumentApiController extends Controller
{
    public function __construct(
        private readonly PortalDocumentPermissionService $permissionService
    ) {
    }

    public function index(Request $request, int $roundId, int $categoryId): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof ThirdPartyUser || ! $this->permissionService->can($user, 'prequalification', 'view')) {
            return response()->json(['message' => 'You do not have permission to view prequalification documents.'], 403);
        }

        $supplierId = $user?->thirdParty?->Id;
        if (! $supplierId) {
            return response()->json(['message' => 'Supplier profile not found.'], 403);
        }

        $docs = PrequalificationApplicationDocument::query()
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->where('CategoryID', $categoryId)
            ->whereNull('DeletedOn')
            ->with('dmsDocument.current')
            ->orderByDesc('CreatedOn')
            ->get();

        return response()->json(['data' => $docs]);
    }

    public function store(StoreApplicationDocumentRequest $request, int $roundId, int $categoryId): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof ThirdPartyUser || ! $this->permissionService->can($user, 'prequalification', 'upload')) {
            return response()->json(['message' => 'You do not have permission to upload prequalification documents.'], 403);
        }

        // DMS DocumentService requires an internal Auth\\User actor, not ThirdPartyUser
        $actor = SystemHelper::user();
        $supplierId = $user?->thirdParty?->Id;
        if (! $supplierId) {
            return response()->json(['message' => 'Supplier profile not found.'], 403);
        }

        $sectionId = (int) $request->input('section_id');
        $fileType = (string) $request->input('file_type');
        $description = (string) $request->input('description', '');

        $application = PrequalificationApplication::query()
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->where('CategoryID', $categoryId)
            ->whereNull('DeletedOn')
            ->latest('CreatedOn')
            ->first();

        $file = $request->file('file');

        try {
            // Upload to DMS under Procurement module
            $repository = RepositoryService::module(ModulesEnum::Procurement);
            $dms = DocumentService::createUpload($repository, $file, $actor);
            $document = $dms->document; // App\Models\DMS\Document

            $link = PrequalificationApplicationDocument::create([
                'SupplierID' => $supplierId,
                'RoundID' => $roundId,
                'CategoryID' => $categoryId,
                'SectionID' => $sectionId,
                'ApplicationID' => $application?->ApplicationID,
                'DocumentId' => $document->DocumentId,
                'FileType' => $fileType,
                'Description' => $description,
                'CreatedBy' => $user->Id,
            ]);

            return response()->json([
                'message' => 'Document uploaded',
                'data' => $link->load('dmsDocument.current'),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PreqApplicationDocument upload failed', [
                'roundId' => $roundId,
                'categoryId' => $categoryId,
                'supplierId' => $supplierId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Upload failed',
                'errors' => ['file' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function destroy(Request $request, int $roundId, int $categoryId, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof ThirdPartyUser || ! $this->permissionService->can($user, 'prequalification', 'delete')) {
            return response()->json(['message' => 'You do not have permission to delete prequalification documents.'], 403);
        }

        $supplierId = $user?->thirdParty?->Id;
        if (! $supplierId) {
            return response()->json(['message' => 'Supplier profile not found.'], 403);
        }

        $doc = PrequalificationApplicationDocument::query()
            ->where('Id', $id)
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->where('CategoryID', $categoryId)
            ->firstOrFail();
        $doc->forceFill(['DeletedOn' => now(), 'DeletedBy' => $user->Id])->save();

        return response()->json(['message' => 'Deleted']);
    }

    public function download(Request $request, int $roundId, int $categoryId, int $id)
    {
        $user = $request->user();
        if (! $user instanceof ThirdPartyUser || ! $this->permissionService->can($user, 'prequalification', 'download')) {
            return response()->json(['message' => 'You do not have permission to download prequalification documents.'], 403);
        }

        $supplierId = $user?->thirdParty?->Id;
        if (! $supplierId) {
            return response()->json(['message' => 'Supplier profile not found.'], 403);
        }

        $record = PrequalificationApplicationDocument::query()
            ->where('Id', $id)
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->where('CategoryID', $categoryId)
            ->whereNull('DeletedOn')
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $document = Document::query()
            ->where('DocumentId', $record->DocumentId)
            ->with('current')
            ->first();

        if (! $document) {
            return response()->json(['message' => 'Document file not found.'], 404);
        }

        try {
            $service = new DocumentService($document);
            $fileName = $document->current?->Name ?? $document->Name ?? ('prequalification-document-' . $document->DocumentId);

            return response($service->getFileContent(false), 200)
                ->header('Content-Type', $document->MimeType ?? 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (\Throwable $e) {
            Log::error('PreqApplicationDocument download failed', [
                'id' => $id,
                'supplierId' => $supplierId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Download failed',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
