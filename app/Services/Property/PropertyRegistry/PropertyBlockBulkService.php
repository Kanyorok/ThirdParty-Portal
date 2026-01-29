<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;
use Exception;

class PropertyBlockBulkService
{
    /**
     * Process bulk block upload from CSV/Excel data
     *
     * Expected columns:
     * - PropertyID (property code or Id)
     * - BlockName
     * - Description (optional)
     */
    public static function processBulkUpload(array $data, User $user): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
            'created_blocks' => [],
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
                if (empty($row['BlockName'] ?? null)) {
                    throw new Exception("Missing required field: BlockName");
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

                // Check if block already exists for this property
                $existingBlock = PropertyBlock::where('PropertyID', $property->Id)
                    ->where('BlockName', $row['BlockName'])
                    ->first();
                if ($existingBlock) {
                    throw new Exception("Block '{$row['BlockName']}' already exists for this property");
                }

                // Create block
                $block = PropertyBlock::create([
                    'PropertyID' => $property->Id,
                    'BlockName' => trim($row['BlockName']),
                    'Description' => isset($row['Description']) ? trim($row['Description']) : '',
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($block)
                    ->event('bulk_create')
                    ->log("Property block created via bulk upload: {$block->BlockName}");

                $results['successful']++;
                $results['created_blocks'][] = $block->Id;
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
