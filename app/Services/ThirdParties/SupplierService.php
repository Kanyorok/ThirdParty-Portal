<?php

namespace App\Services\ThirdParties;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ThirdParies\Supplier;

class SupplierService extends ThirdPartiesService
{
    public function __construct(public SupplierMaster $supplier)
    {
        if (!$supplier->relationLoaded('party')) {
            $supplier->load('party');
        }

        if (!$supplier->party) {
            throw new \RuntimeException("Supplier {$supplier->SupplierID} has no associated ThirdParty record");
        }

        parent::__construct($supplier->party);
    }

    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeSupplier)->firstOr(function () {
            $role = FinanceRole::query()->first();
            if ($role instanceof FinanceRole === false) {
                throw new \RuntimeException("No finance roles found " . __CLASS__);
            }
            $actor = SystemHelper::user();
            return ThirdPartyType::create([
                'FinanceRole' => $role->FinanceRoleID,
                'Code' => ThirdPartyService::TypeSupplier,
                'Description' => 'Supplier',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
        });
    }

    public static function create(
        string $name,
        ?string $tradingName,
        CodeDetail $businessType,
        string $registrationNumber,
        string $taxPIN,
        ?string $vatNumber,
        Locality $locationID,
        ?string $physicalAddress,
        ?string $email,
        ?string $phone,
        ?string $website,
        ?CodeDetail $status,
        ?array $extra,
        User|ThirdPartyUser $actor,
        array $data = []
    ): ThirdParties {
        $service = self::createFromParty(
            party: parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor, $data),
            actor: $actor,
            data: $data
        );

        return $service->party;
    }

    public static function createFromParty(ThirdParties $party, User|ThirdPartyUser $actor, array $data = []): self
    {
        $auditId = ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id;

        $master = SupplierMaster::create([
            'ThirdPartyId' => $party->Id,
            'SupplierID' => self::_ID(),
            'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Pending,
            'IsPrequalified' => false,
            'CreatedBy' => $auditId,
            'ModifiedBy' => $auditId,
        ]);

        Supplier::create([
            'SupplierMasterId' => $master->Id,
            'CategoryId' => $data['supplier_category_id'] ?? $data['category_id'] ?? null,
            'Active_Status' => true,
            'CreatedBy' => $auditId,
            'ModifiedBy' => $auditId,
        ]);

        $service = new self($master);

        $service->addType(
            self::getType(),
            'SupplierMasterId',
            $master->Id,
            $actor
        );

        activity()
            ->causedBy($actor)
            ->performedOn($master)
            ->event('create')
            ->log("Registered Supplier {$master->SupplierID} for {$party->ThirdPartyName}.");

        return $service;
    }

    public static function updateFromParty(ThirdParties $party, User|ThirdPartyUser $actor, array $data = []): void
    {
        $master = SupplierMaster::where('ThirdPartyId', $party->Id)->first();

        if ($master) {
            $auditId = ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id;

            $master->update([
                'ModifiedBy' => $auditId,
            ]);

            Supplier::where('SupplierMasterId', $master->Id)->update([
                'CategoryId' => $data['supplier_category_id'] ?? $data['category_id'] ?? null,
                'ModifiedBy' => $auditId,
            ]);

            activity()
                ->causedBy($actor)
                ->performedOn($master)
                ->event('update')
                ->log("Updated Supplier profile for {$party->ThirdPartyName}.");
        }
    }

    protected static function _ID(): string
    {
        $number = SupplierMaster::withTrashed()->count();
        do {
            $number++;
            $slug = ThirdPartyService::TypeSupplier . Str::padLeft($number, 5, '0');
        } while (SupplierMaster::withTrashed()->where('SupplierID', $slug)->exists());

        return $slug;
    }

    public static function getSupplierDetails($SupplierId)
    {
        return DB::table(DB::raw('t_Suppliers AS s WITH (NOLOCK)'))
            ->join(DB::raw('t_SupplierMaster AS sm WITH (NOLOCK)'), 'sm.Id', '=', 's.SupplierMasterId')
            ->join(DB::raw('t_ThirdParties AS tp WITH (NOLOCK)'), 'tp.Id', '=', 'sm.ThirdPartyId')
            ->select(
                'tp.ThirdPartyName as Name',
                'tp.TradingName',
                DB::raw("COALESCE(tp.Email, '') as Email"),
                DB::raw("COALESCE(tp.Phone, '') as Phone"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                's.CategoryId'
            )
            ->where('s.Id', $SupplierId)
            ->first();
    }

    public static function getSuppliers()
    {
        return DB::table(DB::raw('t_SupplierMaster AS sm WITH (NOLOCK)'))
            ->join(DB::raw('t_ThirdParties AS tp WITH (NOLOCK)'), 'tp.Id', '=', 'sm.ThirdPartyId')
            ->leftJoin(DB::raw('t_Suppliers AS s WITH (NOLOCK)'), 's.SupplierMasterId', '=', 'sm.Id')
            ->select(
                'tp.ThirdPartyName as SupplierName',
                'tp.TradingName',
                DB::raw("COALESCE(tp.Email, '') as Email"),
                DB::raw("COALESCE(tp.Phone, '') as Phone"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                's.CategoryId',
                'sm.Id as SupplierMasterId',
                'sm.ThirdPartyId',
                'sm.SupplierID'
            )
            ->whereNull('sm.DeletedOn')
            ->orderBy('tp.ThirdPartyName', 'asc')
            ->get();
    }
}
