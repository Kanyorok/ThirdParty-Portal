<?php

namespace App\Services\ThirdParties;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierService extends ThirdPartiesService
{
    public function __construct(public SupplierMaster $supplier)
    {
        parent::__construct($supplier->party);
    }

    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeSupplier)->firstOr(function () {
            $role = FinanceRole::query()->first();// todo fix your Finance role
            if ($role instanceof FinanceRole === false) {
                throw new \RuntimeException("No finance roles found " . __CLASS__);
            }
            $actor = SystemHelper::user();
            return ThirdPartyType::create([
                'FinanceRole' => $role->FinanceRoleID,
                'Code' => ThirdPartyService::TypeSupplier,
                'Description' => 'Tenant',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
        });
    }


    public static function create(
        string  $name, ?string $tradingName, CodeDetail $businessType, string $registrationNumber, string $taxPIN, ?string $vatNumber, Locality $locationID, ?string $physicalAddress,
        ?string $email, ?string $phone, ?string $website, ?CodeDetail $status, ?array $extra, User $actor): self
    {
        return self::createFromParty(
            party: parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor),
            actor: $actor
        );
    }

    public static function createFromParty(ThirdParties $party, User $actor): self
    {
        $supplier = SupplierMaster::create([
            'ThirdPartyId' => $party->Id,
            'SupplierID' => self::_ID(),
            'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Pending,
            'IsPrequalified' => false,
            'Extra' => null,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);
        activity()->causedBy($actor)->performedOn($supplier)->event('create')->log("Added Supplier {$supplier->SupplierID} to thirdparty {$party->ThirdPartyName}.");
        $service = new self($supplier);
        $service->addType(self::getType(), SupplierMaster::getPrimaryKey(), $supplier->Id, $actor);
        return $service;
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
            ->join(DB::raw('t_ThirdParties AS tp WITH (NOLOCK)'), 'tp.Id', '=', 's.ThirdPartyID')
            ->select(
                DB::raw('tp.TradingName as Name'),
                DB::raw("COALESCE(tp.Email, '') as Email"),
                DB::raw("COALESCE(tp.Phone, '') as Phone"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                's.CategoryId as CategoryId'
            )
            ->where('s.Id', $SupplierId)
            ->first(); // Return a single object, not a collection
    }

    public static function getSuppliers()
    {
        return DB::table(DB::raw('t_Suppliers AS s WITH (NOLOCK)'))
            ->join(DB::raw('t_ThirdParties AS tp WITH (NOLOCK)'), 'tp.Id', '=', 's.ThirdPartyID')
            ->select(
                DB::raw('tp.TradingName as SupplierName'),
                DB::raw("COALESCE(tp.Email, '') as Email"),
                DB::raw("COALESCE(tp.Phone, '') as Phone"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                's.CategoryId as CategoryId',
                DB::raw('s.Id as SupplierId'),
                DB::raw('s.ThirdPartyID as ThirdPartyId')
            )
            ->whereNull('s.DeletedOn')
            ->orderBy('tp.TradingName', 'asc')
            ->get();
    }

}
