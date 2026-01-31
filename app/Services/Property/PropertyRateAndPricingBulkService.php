<?php

namespace App\Services\Property;

use App\Models\Auth\User;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRateAndPricing;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use Exception;

class PropertyRateAndPricingBulkService
{
    /**
     * Process bulk property rate and pricing upload from CSV/Excel data
     *
     * Expected columns:
     * - PropertyId
     * - BlockId
     * - FloorId
     * - UnitId
     * - Rent
     * - ParkingFee
     * - ServiceCharge
     * - OtherCharges
     * - DepositAmount
     * - CurrencyId
     * - TaxId
     */
    public static function processBulkUpload(array $data, User $user): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
            'created_pricings' => [],
        ];

        foreach ($data as $index => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Trim string values
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        $row[$key] = trim($value);
                    }
                }

                // Validate required fields
                $required_fields = ['PropertyId', 'BlockId', 'FloorId', 'UnitId', 'Rent', 'ParkingFee', 'ServiceCharge', 'OtherCharges', 'DepositAmount', 'CurrencyId', 'TaxId'];
                foreach ($required_fields as $field) {
                    if (empty($row[$field] ?? null)) {
                        throw new Exception("Missing required field: $field");
                    }
                }

                // Resolve PropertyId (try ID first if numeric, then by PropertyCode)
                $property = null;
                if (is_numeric($row['PropertyId'])) {
                    $property = PropertyRegistry::find($row['PropertyId']);
                    if (! $property) {
                        throw new Exception("Error in row " . ($index + 1) . ": PropertyId {$row['PropertyId']} does not exist. Please enter a valid PropertyId.");
                    }
                } else {
                    $property = PropertyRegistry::where('PropertyCode', $row['PropertyId'])->first();
                    if (! $property) {
                        throw new Exception("Error in row " . ($index + 1) . ": PropertyCode '{$row['PropertyId']}' does not exist. Please enter a valid PropertyCode.");
                    }
                }

                // Resolve BlockId (try ID first if numeric, then by BlockName)
                $block = null;
                if (is_numeric($row['BlockId'])) {
                    $block = PropertyBlock::where('PropertyID', $property->Id)->find($row['BlockId']);
                    if (! $block) {
                        throw new Exception("Error in row " . ($index + 1) . ": BlockId {$row['BlockId']} does not exist in Property {$row['PropertyId']}. Please enter a valid BlockId.");
                    }
                } else {
                    $block = PropertyBlock::where('BlockName', $row['BlockId'])
                        ->where('PropertyID', $property->Id)
                        ->first();
                    if (! $block) {
                        throw new Exception("Error in row " . ($index + 1) . ": Block '{$row['BlockId']}' does not exist in Property {$row['PropertyId']}. Please enter a valid BlockName.");
                    }
                }

                // Resolve FloorId (try ID first if numeric, then by FloorLabel)
                $floor = null;
                if (is_numeric($row['FloorId'])) {
                    $floor = PropertyFloor::where('BlockID', $block->Id)->find($row['FloorId']);
                    if (! $floor) {
                        throw new Exception("Error in row " . ($index + 1) . ": FloorId {$row['FloorId']} does not exist in Block {$row['BlockId']}. Please enter a valid FloorId.");
                    }
                } else {
                    $floor = PropertyFloor::where('FloorLabel', $row['FloorId'])
                        ->where('BlockID', $block->Id)
                        ->first();
                    if (! $floor) {
                        throw new Exception("Error in row " . ($index + 1) . ": Floor '{$row['FloorId']}' does not exist in Block {$row['BlockId']}. Please enter a valid FloorLabel.");
                    }
                }

                // Resolve UnitId (try ID first if numeric, then by UnitCode)
                $unit = null;
                if (is_numeric($row['UnitId'])) {
                    $unit = PropertyUnit::where('FloorID', $floor->Id)->find($row['UnitId']);
                    if (! $unit) {
                        throw new Exception("Error in row " . ($index + 1) . ": UnitId {$row['UnitId']} does not exist in Floor {$row['FloorId']}. Please enter a valid UnitId.");
                    }
                } else {
                    $unit = PropertyUnit::where('UnitCode', $row['UnitId'])
                        ->where('FloorID', $floor->Id)
                        ->first();
                    if (! $unit) {
                        throw new Exception("Error in row " . ($index + 1) . ": Unit '{$row['UnitId']}' does not exist in Floor {$row['FloorId']}. Please enter a valid UnitCode.");
                    }
                }

                // Resolve CurrencyId
                $currency = null;
                if (is_numeric($row['CurrencyId'])) {
                    $currency = Currency::find($row['CurrencyId']);
                    if (! $currency) {
                        throw new Exception("Error in row " . ($index + 1) . ": CurrencyId {$row['CurrencyId']} does not exist. Please enter a valid CurrencyId.");
                    }
                } else {
                    $currency = Currency::where('CurrencyCode', $row['CurrencyId'])->first();
                    if (! $currency) {
                        throw new Exception("Error in row " . ($index + 1) . ": Currency '{$row['CurrencyId']}' does not exist. Please enter a valid Currency code.");
                    }
                }

                // Resolve TaxId
                $tax = null;
                if (is_numeric($row['TaxId'])) {
                    $tax = FinanceTaxRuleConfiguration::find($row['TaxId']);
                    if (! $tax) {
                        throw new Exception("Error in row " . ($index + 1) . ": TaxId {$row['TaxId']} does not exist. Please enter a valid TaxId.");
                    }
                } else {
                    $tax = FinanceTaxRuleConfiguration::where('TaxName', $row['TaxId'])->first();
                    if (! $tax) {
                        throw new Exception("Error in row " . ($index + 1) . ": Tax '{$row['TaxId']}' does not exist. Please enter a valid Tax name.");
                    }
                }

                // Validate numeric fields
                $numeric_fields = ['Rent', 'ParkingFee', 'ServiceCharge', 'OtherCharges', 'DepositAmount'];
                foreach ($numeric_fields as $field) {
                    if (! is_numeric($row[$field])) {
                        throw new Exception("Error in row " . ($index + 1) . ": $field must be a numeric value, got '{$row[$field]}'.");
                    }
                    $row[$field] = (float)$row[$field];
                }

                // Check if pricing already exists for this unit
                $existingPricing = PropertyRateAndPricing::where('UnitId', $unit->Id)->first();
                if ($existingPricing) {
                    throw new Exception("Error in row " . ($index + 1) . ": Pricing for Unit '{$unit->UnitCode}' already exists. Please update the existing record instead.");
                }

                // Create pricing record
                $pricing = PropertyRateAndPricing::create([
                    'PropertyId' => $property->Id,
                    'BlockId' => $block->Id,
                    'FloorId' => $floor->Id,
                    'UnitId' => $unit->Id,
                    'Rent' => $row['Rent'],
                    'ParkingFee' => $row['ParkingFee'],
                    'ServiceCharge' => $row['ServiceCharge'],
                    'OtherCharges' => $row['OtherCharges'],
                    'DepositAmount' => $row['DepositAmount'],
                    'CurrencyId' => $currency->Id,
                    'TaxId' => $tax->Id,
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($pricing)
                    ->event('bulk_create')
                    ->log("Property rate and pricing created via bulk upload for unit: {$unit->UnitCode}");

                $results['successful']++;
                $results['created_pricings'][] = $pricing->Id;

            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
