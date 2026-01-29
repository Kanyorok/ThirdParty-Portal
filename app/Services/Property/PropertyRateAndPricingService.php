<?php

namespace App\Services\Property;

use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRateAndPricing;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;

class PropertyRateAndPricingService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PropertyRateAndPricing $propertyRateAndPricing)
    {
    }

    public static function create(
        PropertyRegistry $PropertyId,
        ?PropertyBlock $BlockId,
        ?PropertyFloor $FloorId,
        ?PropertyUnit $UnitId,
        int $Rent,
        int $ParkingFee,
        int $ServiceCharge,
        int $OtherCharges,
        int $DepositAmount,
        Currency $CurrencyId,
        FinanceTaxRuleConfiguration $TaxId,
        $user
    ): self {
        $propertyRateAndPricing = PropertyRateAndPricing::create([
            'PropertyId' => $PropertyId -> Id,
            'BlockId' => $BlockId -> Id,
            'FloorId' => $FloorId -> Id,
            'UnitId' => $UnitId -> Id,
            'Rent' => $Rent,
            'ParkingFee' => $ParkingFee,
            'ServiceCharge' => $ServiceCharge,
            'OtherCharges' => $OtherCharges,
            'DepositAmount' => $DepositAmount,
            'CurrencyId' => $CurrencyId -> Id,
            'TaxId' => $TaxId -> Id,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->performedOn($propertyRateAndPricing)
            ->causedBy($user)
            ->withProperties([
                'PropertyRateAndPricingId' => $propertyRateAndPricing->Id,
                'PropertyId' => $PropertyId->Id,
            ])
            ->log('Property Rate And Pricing Created');

        return new self($propertyRateAndPricing);
    }

    public static function update(
        PropertyRateAndPricing $propertyRateAndPricing,
        PropertyRegistry $PropertyId,
        ?PropertyBlock $BlockId,
        ?PropertyFloor $FloorId,
        ?PropertyUnit $UnitId,
        int $Rent,
        int $ParkingFee,
        int $ServiceCharge,
        int $OtherCharges,
        int $DepositAmount,
        Currency $CurrencyId,
        FinanceTaxRuleConfiguration $TaxId,
        $user
    ): self {
        $propertyRateAndPricing->update([
            'PropertyId' => $PropertyId -> Id,
            'BlockId' => $BlockId -> Id,
            'FloorId' => $FloorId -> Id,
            'UnitId' => $UnitId -> Id,
            'Rent' => $Rent,
            'ParkingFee' => $ParkingFee,
            'ServiceCharge' => $ServiceCharge,
            'OtherCharges' => $OtherCharges,
            'DepositAmount' => $DepositAmount,
            'CurrencyId' => $CurrencyId -> Id,
            'TaxId' => $TaxId -> Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->performedOn($propertyRateAndPricing)
            ->causedBy($user)
            ->withProperties([
                'PropertyRateAndPricingId' => $propertyRateAndPricing->Id,
                'PropertyId' => $PropertyId->Id,
            ])
            ->log('Property Rate And Pricing Updated');

        return new self($propertyRateAndPricing);
    }

    public static function delete(
        PropertyRateAndPricing $propertyRateAndPricing,
        $user
    ): void {
        $propertyRateAndPricing->delete();

        activity()
            ->performedOn($propertyRateAndPricing)
            ->causedBy($user)
            ->withProperties([
                'PropertyRateAndPricingId' => $propertyRateAndPricing->Id,
                'PropertyId' => $propertyRateAndPricing->PropertyId,
            ])
            ->log('Property Rate And Pricing Deleted');
    }
}
