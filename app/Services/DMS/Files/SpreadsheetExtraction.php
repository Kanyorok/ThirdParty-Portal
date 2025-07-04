<?php

namespace App\Services\DMS\Files;

use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class SpreadsheetExtraction extends FileExtraction
{
    public function processContent(): bool
    {
        if (!$this->extension->isSpreadsheet()) {
            return false;
        }
        $name = $this->createTempFile();
        $content = $this->extractTextAsString(storage_path('app/temp') . $name);
        $this->trashTempFile($name);
        if ($content !== '') {
            return $this->handleContent($content);
        }
        return $this->handleContent();
    }

    public function extractTextAsString(string $filePath): string
    {
        $extractedData = $this->extractText($filePath);
        $allText = [];

        foreach ($extractedData as $sheetName => $sheetData) {
            if (!empty($sheetData['all_text'])) {
                $allText[] = $sheetData['all_text'];
            }
        }

        return implode($options['delimiter'] ?? ' ', $allText);
    }

    private function extractText(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $options = [
            'include_empty_cells' => true,
            'include_formulas' => true,
            'sheet_names' => [], // Extract from specific sheets only (empty = all sheets)
            'max_rows' => null,
            'max_columns' => null,
            'delimiter' => ', ', // For joining cell values
        ];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $extractedData = [];

            $worksheets = $this->getWorksheetsToProcess($spreadsheet, $options['sheet_names']);

            foreach ($worksheets as $worksheet) {
                $sheetData = $this->extractFromWorksheet($worksheet, $options);
                $extractedData[$worksheet->getTitle()] = $sheetData;
            }

            return $extractedData;

        } catch (Exception $e) {
            throw new RuntimeException("Failed to extract text from spreadsheet: " . $e->getMessage());
        }
    }

    private function getWorksheetsToProcess(Spreadsheet $spreadsheet, array $sheetNames): array
    {
        if (empty($sheetNames)) {
            return $spreadsheet->getAllSheets();
        }

        $worksheets = [];
        foreach ($sheetNames as $sheetName) {
            try {
                $worksheets[] = $spreadsheet->getSheetByName($sheetName);
            } catch (Exception $e) {
                continue;
            }
        }

        return $worksheets;
    }

    private function extractFromWorksheet(Worksheet $worksheet, array $options): array
    {
        $extractedData = [
            'sheet_name' => $worksheet->getTitle(),
            'rows' => [],
            'all_text' => '',
            'cell_count' => 0,
        ];

        // Get the highest row and column
        $highestRow = $options['max_rows'] ?? $worksheet->getHighestRow();
        $highestColumn = $options['max_columns'] ?? $worksheet->getHighestColumn();

        $allText = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            $hasContent = false;

            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $worksheet->getCell($col . $row);
                $cellValue = $this->getCellValue($cell, $options['include_formulas']);

                if ($cellValue !== '' && $cellValue !== null) {
                    $hasContent = true;
                    $extractedData['cell_count']++;
                    $allText[] = $cellValue;
                }

                if ($options['include_empty_cells'] || $cellValue !== '') {
                    $rowData[$col] = $cellValue;
                }
            }

            if ($hasContent || $options['include_empty_cells']) {
                $extractedData['rows'][$row] = $rowData;
            }
        }

        $extractedData['all_text'] = implode($options['delimiter'], $allText);

        return $extractedData;
    }

    private function getCellValue(Cell $cell, bool $includeFormulas = false): string
    {
        try {
            /*  if ($includeFormulas && $cell->hasFormula()) {
                  return $cell->getFormula();
              }*/

            $value = $cell->getCalculatedValue();
            if (is_bool($value)) {
                return $value ? 'TRUE' : 'FALSE';
            }

            return (string)$value;

        } catch (Exception $e) {
            // If calculation fails, return the raw value
            return (string)$cell->getValue();
        }
    }

}
