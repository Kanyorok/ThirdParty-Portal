<?php

namespace App\Services\Procurement\Prequalification;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\Procurement\Prequalification\ApplicationCategoryStatus;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationApplicationDocument;
use App\Models\Procurement\Prequalification\PrequalificationResult;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\ThirdParty\SupplierCategory;
use App\Models\ThirdParty\SupplierMaster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrequalificationService
{
    /**
     * Get supplier eligibility and master record
     */
    public function getSupplierEligibility($user): array
    {
        $supplierId = null;
        $supplierMaster = null;
        $supplierEligible = false;

        if ($user && $user->thirdParty) {
            $supplierMaster = SupplierMaster::where('ThirdPartyId', $user->thirdParty->Id)->first();
            $supplierId = $supplierMaster?->Id;

            $isSupplierUser = $supplierMaster !== null;
            $isApprovedUser = ($supplierMaster?->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved);

            $supplierEligible = $isSupplierUser && $isApprovedUser;
        }

        return [
            'supplierId' => $supplierId,
            'supplierMaster' => $supplierMaster,
            'eligible' => $supplierEligible,
        ];
    }

    /**
     * Build query for prequalification rounds with filters
     */
    public function buildRoundsQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = PrequalificationRound::query()
            ->with(['sections.masterSection', 'sections.criteria', 'criteria.masterCriteria'])
            ->select('t_PrequalificationRounds.*');

        // Apply status filtering
        $status = $filters['status'] ?? 'open';
        if ($status !== 'all') {
            if ($status === 'open') {
                $query->where('t_PrequalificationRounds.Status', PrequalificationRoundEnum::Open);
            } elseif ($status === 'closed') {
                $query->where('t_PrequalificationRounds.Status', PrequalificationRoundEnum::Closed);
            } elseif ($status === 'draft' || $status === 'd') {
                $query->where('t_PrequalificationRounds.Status', PrequalificationRoundEnum::Draft);
            }
        }

        // Apply search functionality
        $search = $filters['search'] ?? '';
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('t_PrequalificationRounds.Title', 'LIKE', '%' . $search . '%')
                    ->orWhere('t_PrequalificationRounds.Description', 'LIKE', '%' . $search . '%');
            });
        }

        // Apply sorting
        $sortColumn = $filters['sortColumn'] ?? 't_PrequalificationRounds.StartDate';
        $sortOrder = $filters['sortOrder'] ?? 'desc';
        $query->orderBy($sortColumn, $sortOrder);

        return $query;
    }

    /**
     * Get supplier categories for a supplier
     */
    public function getSupplierCategories(?int $thirdPartyId): Collection
    {
        if (! $thirdPartyId) {
            return collect();
        }

        return DB::table('t_ThirdParty_SupplierCategory as tpsc')
            ->join('t_SupplierCategories as sc', 'sc.SupplierCategoryID', '=', 'tpsc.supplier_category_id')
            ->where('tpsc.third_party_id', $thirdPartyId)
            ->whereNull('sc.DeletedOn')
            ->where(function ($q) {
                $q->where('sc.IsActive', 1)->orWhereNull('sc.IsActive');
            })
            ->select('sc.SupplierCategoryID', 'sc.CategoryName', 'sc.Description')
            ->orderBy('sc.CategoryName')
            ->get()
            ->unique('SupplierCategoryID')
            ->values();
    }

    /**
     * Get all active categories
     */
    public function getAllActiveCategories(): Collection
    {
        return DB::table('t_SupplierCategories as sc')
            ->whereNull('sc.DeletedOn')
            ->where(function ($q) {
                $q->where('sc.IsActive', 1)->orWhereNull('sc.IsActive');
            })
            ->select('sc.SupplierCategoryID', 'sc.CategoryName', 'sc.Description')
            ->orderBy('sc.CategoryName')
            ->get();
    }

    /**
     * Get round item category mappings
     */
    public function getRoundItemCategoryMap(Collection $roundIds): Collection
    {
        // Check for mapping table existence
        if (
            Schema::hasTable('t_PrequalificationRoundItemCategory')
            && Schema::hasColumn('t_PrequalificationRoundItemCategory', 'RoundID')
            && Schema::hasColumn('t_PrequalificationRoundItemCategory', 'ItemCategoryID')
        ) {
            return DB::table('t_PrequalificationRoundItemCategory')
                ->whereIn('RoundID', $roundIds)
                ->whereNull('DeletedOn')
                ->get(['RoundID', 'ItemCategoryID'])
                ->groupBy('RoundID')
                ->map(fn ($rows) => $rows->pluck('ItemCategoryID')->filter()->unique()->values());
        } elseif (
            Schema::hasTable('t_PrequalificationRoundItemCategories')
            && Schema::hasColumn('t_PrequalificationRoundItemCategories', 'RoundID')
            && Schema::hasColumn('t_PrequalificationRoundItemCategories', 'ItemCategoryID')
        ) {
            return DB::table('t_PrequalificationRoundItemCategories')
                ->whereIn('RoundID', $roundIds)
                ->whereNull('DeletedOn')
                ->get(['RoundID', 'ItemCategoryID'])
                ->groupBy('RoundID')
                ->map(fn ($rows) => $rows->pluck('ItemCategoryID')->filter()->unique()->values());
        }

        return collect();
    }

    /**
     * Build categories by round
     */
    public function buildCategoriesByRound(
        Collection $roundIds,
        Collection $supplierCategories,
        Collection $roundItemCategoryMap
    ): Collection {
        return $roundIds->mapWithKeys(function ($rid) use ($supplierCategories, $roundItemCategoryMap) {
            // If no mapping exists at all, use supplier categories if present; otherwise fall back to all active
            if ($roundItemCategoryMap->isEmpty()) {
                return [$rid => $supplierCategories->isNotEmpty()
                    ? $supplierCategories
                    : $this->getAllActiveCategories()];
            }

            $itemIds = $roundItemCategoryMap->get($rid, collect());
            if (! $itemIds instanceof Collection) {
                $itemIds = collect($itemIds);
            }

            if ($itemIds->isEmpty()) {
                return [$rid => $supplierCategories->isNotEmpty()
                    ? $supplierCategories
                    : $this->getAllActiveCategories()];
            }

            // Find supplier categories that map to any of the round item categories
            $allowedCatIds = DB::table('t_SupplierCategory_ItemCategory as scic')
                ->whereIn('scic.ItemCategoryID', $itemIds->all())
                ->whereNull('scic.DeletedOn')
                ->pluck('scic.SupplierCategoryID')
                ->unique()
                ->values();

            if ($allowedCatIds->isEmpty()) {
                if ($supplierCategories->isNotEmpty()) {
                    return [$rid => $supplierCategories];
                }

                return [$rid => $this->getAllActiveCategories()];
            }

            $filtered = $supplierCategories->filter(function ($row) use ($allowedCatIds) {
                return $allowedCatIds->contains($row->SupplierCategoryID);
            })->values();

            return [$rid => $filtered];
        });
    }

    /**
     * Get applications for supplier
     */
    public function getSupplierApplications(?int $supplierId, Collection $roundIds): Collection
    {
        if (! $supplierId || $roundIds->isEmpty()) {
            return collect();
        }

        return PrequalificationApplication::query()
            ->where('SupplierID', $supplierId)
            ->whereIn('RoundID', $roundIds)
            ->whereNull('DeletedOn')
            ->select('ApplicationID', 'RoundID', 'CategoryID', 'SubmittedOn', 'Status')
            ->get();
    }

    /**
     * Get category statuses for applications
     */
    public function getCategoryStatuses(Collection $applicationIds): Collection
    {
        if ($applicationIds->isEmpty()) {
            return collect();
        }

        return ApplicationCategoryStatus::query()
            ->whereIn('ApplicationId', $applicationIds)
            ->get()
            ->groupBy('ApplicationId');
    }

    /**
     * Get results for applications
     */
    public function getApplicationResults(Collection $applicationIds): Collection
    {
        if ($applicationIds->isEmpty()) {
            return collect();
        }

        return PrequalificationResult::query()
            ->whereIn('ApplicationID', $applicationIds)
            ->get()
            ->keyBy('ApplicationID');
    }

    /**
     * Map status codes to UI-friendly labels
     */
    public function mapStatus(?string $appStatusCode = null, ?string $catStatusCode = null, ?string $stage = null): string
    {
        $code = $catStatusCode ?? $appStatusCode;
        if (! $code) {
            return 'NOT_APPLIED';
        }

        $code = is_string($code) ? $code : (string) $code;

        return match ($code) {
            'A', 'P' => 'APPROVED',
            'R' => 'REJECTED',
            'V' => 'UNDER_REVIEW',
            'S' => 'SUBMITTED',
            default => $stage && str_contains(strtolower($stage), 'review') ? 'UNDER_REVIEW' : 'SUBMITTED',
        };
    }

    /**
     * Check for duplicate applications
     */
    public function checkDuplicateApplications(int $supplierId, int $roundId, array $categoryIds): array
    {
        $existing = PrequalificationApplication::query()
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->whereNull('DeletedOn')
            ->whereIn('CategoryID', $categoryIds)
            ->pluck('CategoryID')
            ->toArray();

        if (empty($existing)) {
            return [];
        }

        $dupNames = SupplierCategory::whereIn('SupplierCategoryID', $existing)
            ->pluck('CategoryName', 'SupplierCategoryID')
            ->toArray();

        $duplicates = [];
        foreach ($existing as $cid) {
            $duplicates[] = ['id' => $cid, 'name' => $dupNames[$cid] ?? null];
        }

        return $duplicates;
    }

    /**
     * Validate round eligibility for application
     */
    public function validateRoundEligibility(PrequalificationRound $round): array
    {
        $now = now();
        $statusValue = is_object($round->Status) && property_exists($round->Status, 'value')
            ? $round->Status->value
            : (string) $round->Status;

        $isClosed = (string) $statusValue === (string) PrequalificationRoundEnum::Closed->value;
        $isOpen = (string) $statusValue === (string) PrequalificationRoundEnum::Open->value;
        $windowOpen = (! $round->StartDate || $round->StartDate <= $now)
            && (! $round->EndDate || $round->EndDate >= $now);
        $isExpired = $round->EndDate && $round->EndDate < $now;

        $isEligible = ! $isClosed && $isOpen && $windowOpen && ! $isExpired;

        $reason = null;
        $httpCode = 200;

        if (! $isEligible) {
            if ($isExpired) {
                $reason = 'This round has expired.';
                $httpCode = 410;
            } elseif ($isClosed) {
                $reason = 'Applications are closed for this round.';
                $httpCode = 403;
            } elseif (! $isOpen) {
                $reason = 'Round is not open for applications.';
                $httpCode = 403;
            } else {
                $reason = 'Application window is not active.';
                $httpCode = 403;
            }
        }

        return [
            'eligible' => $isEligible,
            'reason' => $reason,
            'httpCode' => $httpCode,
        ];
    }

    /**
     * Create prequalification applications
     */
    public function createApplications(
        int $roundId,
        int $supplierId,
        array $categoryIds,
        int $userId
    ): array {
        $createdIds = [];
        $categoryToApp = [];

        foreach ($categoryIds as $cid) {
            $app = PrequalificationApplication::create([
                'RoundID' => $roundId,
                'SupplierID' => $supplierId,
                'CategoryID' => $cid,
                'Status' => PrequalificationApplicationEnum::Submitted,
                'SubmittedOn' => now(),
                'CreatedBy' => $userId,
            ]);
            $createdIds[] = $app->ApplicationID;
            $categoryToApp[$cid] = $app->ApplicationID;
        }

        return [
            'createdIds' => $createdIds,
            'categoryToApp' => $categoryToApp,
        ];
    }

    /**
     * Attach documents to applications
     */
    public function attachDocuments(
        int $supplierId,
        int $roundId,
        array $categoryToApp,
        int $userId
    ): void {
        foreach ($categoryToApp as $cid => $applicationId) {
            PrequalificationApplicationDocument::query()
                ->where('SupplierID', $supplierId)
                ->where('RoundID', $roundId)
                ->where('CategoryID', $cid)
                ->whereNull('ApplicationID')
                ->update([
                    'ApplicationID' => $applicationId,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);
        }
    }
}
