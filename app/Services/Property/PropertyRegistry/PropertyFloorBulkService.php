<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use Exception;

class PropertyFloorBulkService
{
    /**
     * Process bulk floor upload from CSV/Excel data
     *
     * Expected columns:
     * - PropertyID (property code or Id)
     * - BlockID (block name or Id)
     * - FloorLabel
     * - FloorNotes (optional)
     */
    public static function processBulkUpload(array $data, User $user): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
            'created_floors' => [],
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
                if (empty($row['PropertyID'] ?? null)) {
                    throw new Exception("Missing required field: PropertyID");
                }
                if (empty($row['BlockID'] ?? null)) {
                    throw new Exception("Missing required field: BlockID");
                }
                if (empty($row['FloorLabel'] ?? null)) {
                    throw new Exception("Missing required field: FloorLabel");
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

                // Check if floor already exists
                $existingFloor = PropertyFloor::where('BlockID', $block->Id)
                    ->where('FloorLabel', $row['FloorLabel'])
                    ->first();
                if ($existingFloor) {
                    throw new Exception("Floor '{$row['FloorLabel']}' already exists in this block");
                }

                // Create floor
                $floor = PropertyFloor::create([
                    'PropertyID' => $property->Id,
                    'BlockID' => $block->Id,
                    'FloorLabel' => trim($row['FloorLabel']),
                    'FloorNotes' => isset($row['FloorNotes']) ? trim($row['FloorNotes']) : '',
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($floor)
                    ->event('bulk_create')
                    ->log("Property floor created via bulk upload: {$floor->FloorLabel}");

                $results['successful']++;
                $results['created_floors'][] = $floor->Id;
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
