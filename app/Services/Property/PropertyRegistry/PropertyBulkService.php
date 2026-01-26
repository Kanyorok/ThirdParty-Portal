<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\PropertyManagement\PropertyType;
use App\Models\Core\CategoryMaster;
use Illuminate\Support\Carbon;
use Exception;

class PropertyBulkService
{
    /**
     * Process bulk property upload from CSV/Excel data
     * 
     * Expected columns:
     * - PropertyName
     * - PropertyCode
     * - PropertyType (name or Id)
     * - Category (name or Id)
     * - Owner
     * - AcquisitionDate (YYYY-MM-DD)
     * - CountryId
     * - LocationId
     * - Address
     * - PropertyDescription (optional)
     */
    public static function processBulkUpload(array $data, User $user): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
            'created_properties' => []
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
                $required_fields = ['PropertyName', 'PropertyCode', 'PropertyType', 'Category', 'Owner', 'AcquisitionDate', 'CountryId', 'LocationId', 'Address'];
                foreach ($required_fields as $field) {
                    if (empty($row[$field] ?? null)) {
                        throw new Exception("Missing required field: $field");
                    }
                }

                // Resolve PropertyType (try ID first if numeric, then by PropertyTypeName)
                $propertyType = null;
                if (is_numeric($row['PropertyType'])) {
                    $propertyType = PropertyType::find($row['PropertyType']);
                    if (!$propertyType) {
                        throw new Exception("Error in row " . ($index + 1) . ": PropertyType ID {$row['PropertyType']} does not exist. Please enter a valid PropertyType ID.");
                    }
                } else {
                    $propertyType = PropertyType::where('PropertyTypeName', $row['PropertyType'])->first();
                    if (!$propertyType) {
                        throw new Exception("Error in row " . ($index + 1) . ": PropertyType '{$row['PropertyType']}' does not exist. Please enter a valid PropertyType name.");
                    }
                }

                // Resolve Category (try ID first if numeric, then by Name)
                $category = null;
                if (is_numeric($row['Category'])) {
                    $category = CategoryMaster::where('Type', 'PropertyCategory')->find($row['Category']);
                    if (!$category) {
                        throw new Exception("Error in row " . ($index + 1) . ": Category ID {$row['Category']} does not exist. Please enter a valid Category ID.");
                    }
                } else {
                    $category = CategoryMaster::where('Name', $row['Category'])
                        ->where('Type', 'PropertyCategory')
                        ->first();
                    if (!$category) {
                        throw new Exception("Error in row " . ($index + 1) . ": Category '{$row['Category']}' does not exist. Please enter a valid Category name.");
                    }
                }

                // Resolve Country (try ID first if numeric, then by Name)
                $country = null;
                if (is_numeric($row['CountryId'])) {
                    $country = Country::find($row['CountryId']);
                    if (!$country) {
                        throw new Exception("Error in row " . ($index + 1) . ": Country ID {$row['CountryId']} does not exist. Please enter a valid Country ID.");
                    }
                } else {
                    $country = Country::where('Name', $row['CountryId'])->first();
                    if (!$country) {
                        throw new Exception("Error in row " . ($index + 1) . ": Country '{$row['CountryId']}' does not exist. Please enter a valid Country name.");
                    }
                }

                // Resolve Locality (try ID first if numeric, then by Name)
                $locality = null;
                if (is_numeric($row['LocationId'])) {
                    $locality = Locality::where('CountryId', $country->Id)->find($row['LocationId']);
                    if (!$locality) {
                        throw new Exception("Error in row " . ($index + 1) . ": Locality ID {$row['LocationId']} does not exist in Country {$row['CountryId']}. Please enter a valid Locality ID.");
                    }
                } else {
                    $locality = Locality::where('Name', $row['LocationId'])
                        ->where('CountryId', $country->Id)
                        ->first();
                    if (!$locality) {
                        throw new Exception("Error in row " . ($index + 1) . ": Locality '{$row['LocationId']}' does not exist in Country {$row['CountryId']}. Please enter a valid Locality name.");
                    }
                }

                // Check if property already exists
                $existingProperty = PropertyRegistry::where('PropertyCode', $row['PropertyCode'])->first();
                if ($existingProperty) {
                    throw new Exception("Property code already exists: {$row['PropertyCode']}");
                }

                // Parse acquisition date (handle Excel numeric dates and string dates)
                $acquisitionDate = self::parseDate($row['AcquisitionDate']);

                // Create property
                $property = PropertyRegistry::create([
                    'PropertyName' => $row['PropertyName'],
                    'PropertyCode' => $row['PropertyCode'],
                    'PropertyType' => $propertyType->Id,
                    'Category' => $category->Id,
                    'Owner' => $row['Owner'],
                    'AcquisitionDate' => $acquisitionDate,
                    'CountryId' => $country->Id,
                    'LocationId' => $locality->ID,
                    'Address' => $row['Address'],
                    'PropertyDescription' => $row['PropertyDescription'] ?? '',
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                    'IsActive' => 1
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($property)
                    ->event('bulk_create')
                    ->log("Property created via bulk upload: {$property->PropertyName}");

                $results['successful']++;
                $results['created_properties'][] = $property->Id;

            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Parse date from Excel numeric format or string format
     * Excel dates are stored as integers (days since 1900-01-01)
     *
     * @param mixed $date
     * @return \Carbon\Carbon
     */
    private static function parseDate($date)
    {
        // If it's already a Carbon instance, return it
        if ($date instanceof Carbon) {
            return $date;
        }

        // If it's a numeric value (Excel date), convert it
        if (is_numeric($date)) {
            // Excel date serial number (days since 1900-01-01)
            // Adjust for Excel's leap year bug (1900 is not a leap year in Excel)
            $excelDateBase = 25569; // Days between 1900-01-01 and 1970-01-01
            $timestamp = ($date - $excelDateBase) * 86400; // 86400 seconds per day
            return Carbon::createFromTimestamp($timestamp);
        }

        // Try common date formats
        $formats = ['Y-m-d', 'd-m-Y', 'm/d/Y', 'd/m/Y', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date);
            } catch (\Exception $e) {
                continue;
            }
        }

        // If all else fails, throw an exception
        throw new Exception("Unable to parse date: {$date}. Expected format: YYYY-MM-DD");
    }
}
