<?php

namespace App\Repositories\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\ThirdParty\SupplierMaster;
use App\Repositories\ThirdParty\Contracts\SupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SupplierRepository implements SupplierRepositoryInterface
{
    protected const CACHE_PREFIX = 'supplier';
    protected const CACHE_TTL = 3600;

    public function findById(int $id): ?SupplierMaster
    {
        return Cache::remember(
            $this->getCacheKey($id),
            self::CACHE_TTL,
            fn () => SupplierMaster::with(['party', 'categories'])->find($id)
        );
    }

    public function findByThirdPartyId(int $thirdPartyId): ?SupplierMaster
    {
        return Cache::remember(
            $this->getCacheKey('tp_' . $thirdPartyId),
            self::CACHE_TTL,
            fn () => SupplierMaster::where('ThirdPartyId', $thirdPartyId)
                ->with(['party', 'categories'])
                ->first()
        );
    }

    public function create(array $data): SupplierMaster
    {
        $supplier = SupplierMaster::create($data);
        $this->clearCache($supplier->Id);

        return $supplier->load(['party', 'categories']);
    }

    public function update(SupplierMaster $supplier, array $data): SupplierMaster
    {
        $supplier->update($data);
        $this->clearCache($supplier->Id);

        return $supplier->fresh(['party', 'categories']);
    }

    public function updateApprovalStatus(
        SupplierMaster $supplier,
        ThirdPartyApprovalStatusEnum $status
    ): SupplierMaster {
        $supplier->update(['ApprovalStatus' => $status]);
        $this->clearCache($supplier->Id);

        return $supplier->fresh();
    }

    public function attachCategories(SupplierMaster $supplier, array $categoryIds): void
    {
        if (! empty($categoryIds)) {
            $supplier->categories()->attach($categoryIds);
            $this->clearCache($supplier->Id);
        }
    }

    public function syncCategories(SupplierMaster $supplier, array $categoryIds): void
    {
        $supplier->categories()->sync($categoryIds);
        $this->clearCache($supplier->Id);
    }

    public function getApprovedSuppliers(): Collection
    {
        return SupplierMaster::where('ApprovalStatus', ThirdPartyApprovalStatusEnum::Approved)
            ->with(['party'])
            ->get();
    }

    public function getPendingSuppliers(): Collection
    {
        return SupplierMaster::where('ApprovalStatus', ThirdPartyApprovalStatusEnum::Pending)
            ->with(['party'])
            ->get();
    }

    protected function getCacheKey(int|string $id): string
    {
        return self::CACHE_PREFIX . ":{$id}";
    }

    protected function clearCache(int $id): void
    {
        Cache::forget($this->getCacheKey($id));
        Cache::tags([self::CACHE_PREFIX])->flush();
    }
}
