<?php

namespace App\Services\Procurement\Prequalification\Api;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest;
use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Services\Procurement\Prequalification\PrequalificationService;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PrequalificationApplicationApiService
{
    public function __construct(private PrequalificationService $prequalificationService)
    {
    }

    public function resolveSupplierContext(?AuthenticatableContract $user): array
    {
        if (! $user instanceof User && ! $user instanceof ThirdPartyUser) {
            return [
                'user' => null,
                'thirdPartyId' => null,
                'supplierId' => null,
                'supplierMaster' => null,
                'eligible' => false,
            ];
        }

        $eligibility = $this->prequalificationService->getSupplierEligibility($user);

        return [
            'user' => $user,
            'thirdPartyId' => $user->third_party_id ?? ($user->thirdParty?->Id ?? null),
            'supplierId' => $eligibility['supplierId'],
            'supplierMaster' => $eligibility['supplierMaster'],
            'eligible' => $eligibility['eligible'],
        ];
    }

    public function fetchRounds(Request $request, array $context): LengthAwarePaginator
    {
        $filters = [
            'status' => $request->get('status', 'open'),
            'search' => $request->get('q', ''),
            'sortColumn' => $request->get('sortColumn', 't_PrequalificationRounds.StartDate'),
            'sortOrder' => $request->get('sortOrder', 'desc'),
        ];

        $page = max(1, (int) $request->get('page', 1));
        $pageSize = max(1, min(100, (int) $request->get('pageSize', 10)));

        $query = $this->prequalificationService->buildRoundsQuery($filters);
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        $roundIds = collect($paginator->items())->pluck('RoundID')->filter()->values();
        $supplierCategories = $context['thirdPartyId']
            ? $this->prequalificationService->getSupplierCategories($context['thirdPartyId'])
            : collect();
        $roundItemCategoryMap = $this->prequalificationService->getRoundItemCategoryMap($roundIds);
        $categoriesByRound = $this->prequalificationService->buildCategoriesByRound($roundIds, $supplierCategories, $roundItemCategoryMap);
        $applications = $context['supplierId']
            ? $this->prequalificationService->getSupplierApplications($context['supplierId'], $roundIds)
            : collect();
        $applicationsByKey = $applications->keyBy(fn (PrequalificationApplication $application) => "{$application->RoundID}:{$application->CategoryID}");

        $mapped = $paginator->getCollection()->map(function (PrequalificationRound $round) use ($categoriesByRound, $applicationsByKey, $context) {
            $roundCategories = $categoriesByRound->get($round->RoundID, collect());

            $categories = $roundCategories->map(function ($category) use ($round, $applicationsByKey) {
                $key = "{$round->RoundID}:{$category->SupplierCategoryID}";
                $application = $applicationsByKey->get($key);

                return [
                    'id' => (int) $category->SupplierCategoryID,
                    'name' => $category->CategoryName,
                    'hasApplied' => (bool) $application,
                    'status' => $application
                        ? ($application->Status->value ?? PrequalificationApplicationEnum::Submitted->value)
                        : 'NOT_APPLIED',
                    'applicationId' => $application ? (string) $application->ApplicationID : null,
                ];
            });

            $eligibility = $this->prequalificationService->validateRoundEligibility($round);
            $hasUnapplied = $categories->contains(fn ($category) => ! ($category['hasApplied'] ?? false));
            $hasCategories = $categories->isNotEmpty();

            $dateNow = now()->startOfDay();
            $isExpired = $round->EndDate && $round->EndDate < $dateNow;

            $appliedCategories = $categories->where('hasApplied', true)->values();
            $availableCategories = $categories->where('hasApplied', false)->values();

            return [
                'id' => (int) $round->RoundID,
                'title' => $round->Title,
                'description' => $round->Description,
                'startDate' => $round->StartDate?->format('Y-m-d'),
                'endDate' => $round->EndDate?->format('Y-m-d'),
                'maxVendors' => $round->MaxVendors,
                'categories' => $categories->values(),
                'appliedCategories' => $appliedCategories,
                'availableCategories' => $availableCategories,
                'categoryCount' => $categories->count(),
                'unappliedCount' => $categories->where('hasApplied', false)->count(),
                'status' => $this->buildStatusPayload($round, $isExpired),
                'supplierEligible' => (bool) $context['eligible'],
                'eligibility' => [
                    'eligible' => (bool) $eligibility['eligible'],
                    'reason' => $eligibility['reason'],
                ],
                'canApply' => $context['eligible'] && $eligibility['eligible'] && $hasCategories && $hasUnapplied && ! $isExpired,
                'isExpired' => $isExpired,
            ];
        });

        $paginator->setCollection($mapped->values());

        return $paginator;
    }

    public function submitApplications(StorePrequalificationApplicationRequest $request, array $context, int $actorId): array
    {
        if (! $context['supplierId']) {
            return [];
        }

        $categoryIds = array_values(array_unique(array_filter($request->validated('category_ids') ?? [])));

        return $this->prequalificationService->createApplications(
            $request->validated('round_id'),
            $context['supplierId'],
            $categoryIds,
            $actorId,
        )['createdIds'] ?? [];
    }

    private function buildStatusPayload(PrequalificationRound $round, bool $isExpired): array
    {
        $status = $this->resolveStatusEnum($round, $isExpired);

        if ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
                'badgeClass' => $status->getBadgeClass(),
            ];
        }

        return [
            'value' => (string) $round->Status,
            'label' => (string) $round->Status,
            'badgeClass' => 'bg-secondary',
        ];
    }

    private function resolveStatusEnum(PrequalificationRound $round, bool $isExpired): ?PrequalificationRoundEnum
    {
        if ($isExpired) {
            return PrequalificationRoundEnum::Expired;
        }

        return $round->Status instanceof PrequalificationRoundEnum
            ? $round->Status
            : PrequalificationRoundEnum::tryFrom((string) $round->Status);
    }
}
