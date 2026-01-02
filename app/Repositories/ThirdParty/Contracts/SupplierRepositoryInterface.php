<?php

namespace App\Repositories\ThirdParty\Contracts;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\ThirdParty\SupplierMaster;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface
{
    public function findById(int $id): ?SupplierMaster;

    public function findByThirdPartyId(int $thirdPartyId): ?SupplierMaster;

    public function create(array $data): SupplierMaster;

    public function update(SupplierMaster $supplier, array $data): SupplierMaster;

    public function updateApprovalStatus(
        SupplierMaster $supplier,
        ThirdPartyApprovalStatusEnum $status
    ): SupplierMaster;

    public function attachCategories(SupplierMaster $supplier, array $categoryIds): void;

    public function syncCategories(SupplierMaster $supplier, array $categoryIds): void;

    public function getApprovedSuppliers(): Collection;

    public function getPendingSuppliers(): Collection;
}
