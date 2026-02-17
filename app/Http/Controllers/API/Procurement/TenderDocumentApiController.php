<?php

namespace App\Http\Controllers\API\Procurement;

use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentRelation;
use App\Models\Procurement\Tender;
use App\Services\DMS\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderDocumentApiController extends Controller
{
    private const VISIBLE_STATUSES = [
        TenderStatusEnum::Published->value,
        TenderStatusEnum::OpeningInProgress->value,
    ];

    public function download(Request $request, int $tenderId, string $documentId)
    {
        try {
            $tender = Tender::find($tenderId);
            if (! $tender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tender not found.',
                ], 404);
            }

            $document = Document::where('DocumentId', $documentId)->first();
            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            $relationExists = DocumentRelation::query()
                ->where('DocumentId', $document->Id)
                ->where('Related', Tender::getPrimaryKey())
                ->where('RelatedID', $tender->Id)
                ->whereNull('DeletedOn')
                ->exists();

            if (! $relationExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found for this tender.',
                ], 404);
            }

            $user = Auth::guard('third_party')->user()
                ?? Auth::guard('sanctum')->user()
                ?? Auth::user();
            $thirdPartyId = $this->resolveThirdPartyId($request, $user);
            $supplierIds = $this->resolveSupplierIds($thirdPartyId);

            if (! $this->canAccessTender($tender, $supplierIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this tender.',
                ], 403);
            }

            $service = new DocumentService($document);
            $filename = $document->current?->Name ?? $document->Name ?? ('tender-document-' . $document->DocumentId);

            return response($service->getFileContent(false), 200)
                ->header('Content-Type', $service->type->getMimeType())
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function resolveThirdPartyId(Request $request, $user = null): ?int
    {
        if ($request->filled('third_party_id')) {
            return (int)$request->input('third_party_id');
        }

        if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
            return (int)$user->ThirdPartyId;
        }

        if ($user && property_exists($user, 'ThirdPartyId') && $user->ThirdPartyId) {
            return (int)$user->ThirdPartyId;
        }

        return null;
    }

    private function resolveSupplierIds(?int $thirdPartyId): array
    {
        if (! $thirdPartyId) {
            return [];
        }

        return DB::table('t_Suppliers')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->where('t_SupplierMaster.ThirdPartyId', $thirdPartyId)
            ->whereNull('t_Suppliers.DeletedOn')
            ->pluck('t_Suppliers.Id')
            ->unique()
            ->values()
            ->all();
    }

    private function canAccessTender(Tender $tender, array $supplierIds): bool
    {
        $status = strtolower(trim((string)$tender->getRawOriginal('Status')));
        $type = strtolower(trim((string)$tender->getRawOriginal('TenderType')));

        if ($type === TenderTypeEnum::Open->value) {
            return in_array($status, self::VISIBLE_STATUSES, true);
        }

        if ($type !== TenderTypeEnum::Restricted->value) {
            return false;
        }

        if (empty($supplierIds)) {
            return false;
        }

        return DB::table('t_TenderInvitations')
            ->whereIn('SupplierId', $supplierIds)
            ->where('TenderId', $tender->Id)
            ->whereNull('DeletedOn')
            ->exists();
    }
}
