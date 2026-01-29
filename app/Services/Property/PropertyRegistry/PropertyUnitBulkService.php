<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use Exception;

class PropertyUnitBulkService
{
    /**
     * Process bulk unit upload from CSV/Excel data
     *
     * Expected columns:
     * - PropertyID (property code or Id)
     * - BlockID (block name or Id)
     * - FloorID (floor label or Id)
     * - UnitCode
     * - UnitSize (integer)
     * - IsRentable (1/0 or Yes/No)
     * - CurrentStatus (1/0 or Yes/No)
     * - Remarks (optional)
     */
    public static function processBulkUpload(array $data, User $user): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
            'created_units' => [],
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
                $required_fields = ['PropertyID', 'BlockID', 'FloorID', 'UnitCode', 'UnitSize'];
                foreach ($required_fields as $field) {
                    if (empty($row[$field] ?? null)) {
                        throw new Exception("Missing required field: $field");
                    }
                }

                // Resolve PropertyID (try ID first if numeric, then by PropertyCode)
                $property = null;
                if (is_numeric($row['PropertyID'])) {
                    $property = PropertyRegistry::find($row['PropertyID']);
                    if (! $property) {
                        throw new Exception("Error in row " . ($index + 1) . ": PropertyID {$row['PropertyID']} does not exist. Please enter a valid PropertyID.");
                    }
                } else {
                    $property = PropertyRegistry::where('PropertyCode', $row['PropertyID'])->first();
                    if (! $property) {
                        throw new Exception("Error in row " . ($index + 1) . ": PropertyCode '{$row['PropertyID']}' does not exist. Please enter a valid PropertyCode.");
                    }
                }

                // Resolve BlockID (try ID first if numeric, then by BlockName)
                $block = null;
                if (is_numeric($row['BlockID'])) {
                    $block = PropertyBlock::where('PropertyID', $property->Id)->find($row['BlockID']);
                    if (! $block) {
                        throw new Exception("Error in row " . ($index + 1) . ": BlockID {$row['BlockID']} does not exist in Property {$row['PropertyID']}. Please enter a valid BlockID.");
                    }
                } else {
                    $block = PropertyBlock::where('BlockName', $row['BlockID'])
                        ->where('PropertyID', $property->Id)
                        ->first();
                    if (! $block) {
                        throw new Exception("Error in row " . ($index + 1) . ": Block '{$row['BlockID']}' does not exist in Property {$row['PropertyID']}. Please enter a valid BlockName.");
                    }
                }

                // Resolve FloorID (try ID first if numeric, then by FloorLabel)
                $floor = null;
                if (is_numeric($row['FloorID'])) {
                    $floor = PropertyFloor::where('BlockID', $block->Id)->find($row['FloorID']);
                    if (! $floor) {
                        throw new Exception("Error in row " . ($index + 1) . ": FloorID {$row['FloorID']} does not exist in Block {$row['BlockID']}. Please enter a valid FloorID.");
                    }
                } else {
                    $floor = PropertyFloor::where('FloorLabel', $row['FloorID'])
                        ->where('BlockID', $block->Id)
                        ->first();
                    if (! $floor) {
                        throw new Exception("Error in row " . ($index + 1) . ": Floor '{$row['FloorID']}' does not exist in Block {$row['BlockID']}. Please enter a valid FloorLabel.");
                    }
                }

                // Check if unit already exists
                $existingUnit = PropertyUnit::where('PropertyID', $property->Id)
                    ->where('BlockID', $block->Id)
                    ->where('FloorID', $floor->Id)
                    ->where('UnitCode', $row['UnitCode'])
                    ->first();
                if ($existingUnit) {
                    throw new Exception("Unit '{$row['UnitCode']}' already exists in this location");
                }

                // Validate UnitSize is integer
                if (! is_numeric($row['UnitSize'])) {
                    throw new Exception("UnitSize must be a number");
                }

                // Parse boolean fields
                $isRentable = self::parseBoolean($row['IsRentable'] ?? 0);
                $currentStatus = self::parseBoolean($row['CurrentStatus'] ?? 0);

                // Create unit
                $unit = PropertyUnit::create([
                    'PropertyID' => $property->Id,
                    'BlockID' => $block->Id,
                    'FloorID' => $floor->Id,
                    'UnitCode' => $row['UnitCode'],
                    'UnitSize' => (int)$row['UnitSize'],
                    'IsRentable' => $isRentable,
                    'CurrentStatus' => $currentStatus,
                    'Remarks' => $row['Remarks'] ?? '',
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($unit)
                    ->event('bulk_create')
                    ->log("Property unit created via bulk upload: {$unit->UnitCode}");

                $results['successful']++;
                $results['created_units'][] = $unit->Id;
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

    /**
     * Parse boolean value from various formats
     */
    private static function parseBoolean($value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $value = strtolower(trim((string)$value));

        if (in_array($value, ['1', 'yes', 'true', 'on'], true)) {
            return 1;
        }

        return 0;
    }
}
