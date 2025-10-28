<?php

namespace App\Http\Controllers\API\Procurement\Prequalification;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StoreApplicationDocumentRequest;
use App\Models\DMS\Repository;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationApplicationDocument;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use App\Helpers\SystemHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PreqApplicationDocumentApiController extends Controller
{
    public function index(Request $request, int $roundId, int $categoryId): JsonResponse
    {
        $user = $request->user();
        $supplierId = $user?->thirdParty?->Id;
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
    // DMS DocumentService requires an internal Auth\\User actor, not ThirdPartyUser
    $actor = SystemHelper::user();
        $supplierId = $user?->thirdParty?->Id;

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
                'data' => $link->load('dmsDocument.current')
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
        $supplierId = $user?->thirdParty?->Id;
        $doc = PrequalificationApplicationDocument::query()
            ->where('Id', $id)
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->where('CategoryID', $categoryId)
            ->firstOrFail();
        $doc->forceFill(['DeletedOn' => now(), 'DeletedBy' => $user->Id])->save();
        return response()->json(['message' => 'Deleted']);
    }
}
