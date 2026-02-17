<?php

namespace App\Http\Controllers\Procurement\Prequalification\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest;
use App\Http\Resources\Procurement\PrequalificationRoundResource;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Services\Procurement\Prequalification\Api\PrequalificationApplicationApiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrequalificationApplicationController extends Controller
{
    public function __construct(private PrequalificationApplicationApiService $apiService)
    {
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $context = $this->apiService->resolveSupplierContext(Auth::user());

        try {
            $rounds = $this->apiService->fetchRounds($request, $context);

            return response()->json([
                'data' => $rounds->items(),
                'meta' => $this->buildMetaPayload($request, $rounds),
                'context' => $this->buildContextPayload($context),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => [
                    'user_id' => $context['user']->Id ?? null,
                    'third_party_id' => $context['thirdPartyId'] ?? null,
                ],
            ]);

            return response()->json(['message' => 'Failed to fetch rounds.'], 500);
        }
    }

    public function apiShow(PrequalificationRound $round): JsonResponse
    {
        $context = $this->apiService->resolveSupplierContext(Auth::user());

        try {
            $round->load(['sections.masterSection', 'sections.criteria.masterCriteria', 'applications']);

            return (new PrequalificationRoundResource($round))
                ->additional(['context' => $this->buildContextPayload($context)])
                ->response();
        } catch (\Throwable $e) {
            Log::error('Prequalification apiShow failed', [
                'round' => $round->RoundID ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Failed to fetch round.'], 500);
        }
    }

    public function store(StorePrequalificationApplicationRequest $request): JsonResponse
    {
        $context = $this->apiService->resolveSupplierContext(Auth::user());

        if (! $context['user']) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (! $context['supplierMaster'] || ! $context['supplierId']) {
            return response()->json(['error' => 'Supplier profile incomplete'], 400);
        }

        if (! $context['eligible']) {
            return response()->json(['error' => 'Supplier profile not approved'], 403);
        }

        $categoryIds = collect($request->validated('category_ids'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($categoryIds)) {
            return response()->json(['error' => 'Please select at least one supplier category.'], 422);
        }

        $actorId = (int) ($context['user']->Id ?? $context['user']->user_id ?? $context['user']->id);

        try {
            $createdIds = DB::transaction(fn () => $this->apiService->submitApplications($request, $context, $actorId));

            return response()->json([
                'message' => 'Applications submitted successfully.',
                'ids' => $createdIds,
                'context' => $this->buildContextPayload($context),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Prequalification application submission failed', [
                'round_id' => $request->validated('round_id'),
                'supplier_id' => $context['supplierId'] ?? null,
                'eligible' => $context['eligible'] ?? false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Submission failed.'], 500);
        }
    }

    private function buildMetaPayload(Request $request, LengthAwarePaginator $paginator): array
    {
        return [
            'page' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'status' => $request->get('status', 'open'),
            'filters' => [
                'q' => $request->get('q', ''),
            ],
        ];
    }

    private function buildContextPayload(array $context): array
    {
        return [
            'guest' => ! (bool) $context['user'],
            'eligible' => (bool) ($context['eligible'] ?? false),
            'thirdPartyId' => $context['thirdPartyId'] ?? null,
            'supplierId' => $context['supplierId'] ?? null,
        ];
    }
}
