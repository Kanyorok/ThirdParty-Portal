<?php

namespace App\Imports\HR;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

trait ImportHelper
{
    protected function cleanValue($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        return $value;
    }

    protected function parseDate($value): ?string
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $dateTime = ExcelDate::excelToDateTimeObject((float)$value);

                return Carbon::instance($dateTime)->format('Y-m-d');
            }

            return Carbon::parse((string)$value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseDateTime($value): ?string
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $dateTime = ExcelDate::excelToDateTimeObject((float)$value);

                return Carbon::instance($dateTime)->format('Y-m-d H:i:s');
            }

            return Carbon::parse((string)$value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseBoolean($value): ?bool
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return ((int)$value) === 1;
        }
        $normalized = strtolower(trim((string)$value));
        if (in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true)) {
            return true;
        }
        if (in_array($normalized, ['0', 'false', 'no', 'n', 'off'], true)) {
            return false;
        }

        return null;
    }

    protected function parseSegment($value): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            return (int)$value;
        }
        $upper = strtoupper((string)$value);
        if (preg_match('/Q\\s*([1-4])/', $upper, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/H\\s*([1-2])/', $upper, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/(\\d+)/', $upper, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    protected function normalizeGender($value): ?string
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        $normalized = strtolower((string)$value);
        if (in_array($normalized, ['m', 'male'], true)) {
            return 'Male';
        }
        if (in_array($normalized, ['f', 'female'], true)) {
            return 'Female';
        }
        if (in_array($normalized, ['other', 'o'], true)) {
            return 'Other';
        }

        return null;
    }
}
