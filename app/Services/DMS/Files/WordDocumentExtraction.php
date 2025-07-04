<?php

namespace App\Services\DMS\Files;

use Log;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Throwable;

class WordDocumentExtraction extends FileExtraction
{
    public function processContent(): bool
    {
        if (!$this->extension->isDocument()) {
            return false;
        }
        $name = $this->createTempFile();
        $content = $this->extractDocumentText(storage_path('app/temp') . $name);
        $this->trashTempFile($name);
        if ($content !== '') {
            return $this->handleContent($content);
        }
        return $this->handleContent();
    }

    private function extractDocumentText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }
        $reader = $this->type->getDocumentType();
        if (empty($reader)) {
            return '';
        }
        $phpWord = IOFactory::load($filePath, $reader);

        $text = '';

        // Loop through all sections
        foreach ($phpWord->getSections() as $section) {
            // Get elements in the section
            foreach ($section->getElements() as $element) {
                $text .= $this->extractElementText($element);
            }
        }

        return trim($text);
    }

    private function extractElementText(AbstractElement $element): string
    {
        $text = '';
        try {

            // Handle TextRun elements specifically
            if ($element instanceof Text) {
                $text .= $element->getText() . ' ';
            } elseif ($element instanceof TextRun) {
                foreach ($element->getElements() as $childElement) {
                    $text .= $this->extractElementText($childElement);
                }
            } elseif ($element instanceof TextBreak) {
                $text .= "\n";
            } elseif (method_exists($element, 'getElements')) {
                // Handle containers with child elements
                foreach ($element->getElements() as $childElement) {
                    $text .= $this->extractElementText($childElement);
                }
            } elseif (method_exists($element, 'getText')) {
                // Safely get text and ensure it's a string
                $elementText = $element->getText();
                if (is_string($elementText)) {
                    $text .= $elementText . ' ';
                } else if ($elementText instanceof TextRun) {
                    foreach ($elementText->getElements() as $childElement) {
                        $text .= $this->extractElementText($childElement);
                    }
                }
            } elseif (method_exists($element, 'getContent')) {
                // Safely get content and ensure it's a string
                $elementContent = $element->getContent();
                if (is_string($elementContent)) {
                    $text .= $elementContent . ' ';
                }
            }
        } catch (Throwable $e) {
            // Log the error but continue processing other elements
            Log::warning('Error extracting text from element: ' . get_class($element), [
                'error' => $e->getMessage()
            ]);
        }

        return $text;


    }

}
