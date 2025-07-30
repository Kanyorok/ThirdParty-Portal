<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\BusinessTypeEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('t_Suppliers') && DB::table('t_Suppliers')->count() > 0) {
            $suppliers = DB::table('t_Suppliers')->get();

            foreach ($suppliers as $supplier) {
                DB::table('t_Suppliers')->insert([
                    'ThirdPartyName' => $supplier->SupplierName,
                    'Email' => $supplier->ContactEmail,
                    'Phone' => $supplier->ContactPhone,
                    'PhysicalAddress' => $supplier->Address,
                    'CategoryId' => $supplier->CategoryId,
                    'IsPrequalified' => $supplier->IsPrequalified,
                    'VATNumber' => null,
                    'TaxPIN' => null,
                    'RegistrationNumber' => null,
                    'Country' => 'Kenya',
                    'Website' => null,

                    'ThirdPartyType' => ThirdPartyTypeEnum::Supplier->value,
                    'TradingName' => $supplier->SupplierName,
                    'BusinessType' => BusinessTypeEnum::LimitedLiabilityCompany->value,
                    'Status' => ThirdPartyStatusEnum::Active->value,
                    'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Approved->value,

                    'CreatedBy' => $supplier->CreatedBy,
                    'ModifiedBy' => $supplier->ModifiedBy,
                    'DeletedBy' => $supplier->DeletedBy,
                    'CreatedOn' => $supplier->CreatedOn,
                    'ModifiedOn' => $supplier->ModifiedOn,
                    'DeletedOn' => $supplier->DeletedOn,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('t_Suppliers')->where('ThirdPartyType', ThirdPartyTypeEnum::Supplier->value)->delete();
    }
};
