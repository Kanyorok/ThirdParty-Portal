<?php

namespace App\Services\DMS\Files;

use Illuminate\Support\Facades\Storage;
use Log;
use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\RichText\BreakElement;
use PhpOffice\PhpPresentation\Shape\RichText\Run;
use PhpOffice\PhpPresentation\Shape\RichText\TextElement;
use Throwable;

class PresentationExtraction extends FileExtraction
{
    public function processContent(): bool
    {
        if (!$this->extension->isPresentation()) {
            return false;
        }
        $name = $this->createTempFile();
        $content = $this->extractText(Storage::disk('temp')->path($name));
        $this->trashTempFile($name);
        if ($content !== '') {
            return $this->handleContent($content);
        }
        return $this->handleContent();
    }

    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }
        try {
            $presentation = IOFactory::load($filePath);
            $text = '';
            foreach ($presentation->getAllSlides() as $slide) {
                /* if ($slide->getName()) {
                     $text .= "Slide: " . $slide->getName() . "\n";
                 }*/
                foreach ($slide->getShapeCollection() as $shape) {
                    $text .= $this->extractShapeText($shape);
                }
            }
            return trim($text);
        } catch (Throwable $e) {
            Log::error('Error extracting text from presentation: ' . $e->getMessage());
            return '';
        }
    }

    private function extractShapeText(AbstractShape $shape): string
    {
        $text = '';
        try {
            if ($shape instanceof RichText) {
                foreach ($shape->getParagraphs() as $paragraph) {
                    foreach ($paragraph->getRichTextElements() as $element) {
                        $text .= $this->extractRichTextElement($element);
                    }
                }
            } elseif (method_exists($shape, 'getText')) {
                $shapeText = $shape->getText();
                if (is_string($shapeText)) {
                    $text .= $shapeText . ' ';
                }
            } elseif (method_exists($shape, 'getRichTextElements')) {
                foreach ($shape->getRichTextElements() as $element) {
                    $text .= $this->extractRichTextElement($element);
                }
            }
        } catch (Throwable $e) {
            Log::warning('Error extracting text from shape: ' . get_class($shape), [
                'error' => $e->getMessage()
            ]);
        }
        return $text;
    }

    private function extractRichTextElement($element): string
    {
        $text = '';
        try {
            if ($element instanceof TextElement) {
                $text .= $element->getText() . ' ';
            } elseif ($element instanceof Run) {
                $text .= $element->getText() . ' ';
            } elseif ($element instanceof BreakElement) {
                $text .= "\n";
            } elseif (method_exists($element, 'getText')) {
                $elementText = $element->getText();
                if (is_string($elementText)) {
                    $text .= $elementText . ' ';
                }
            }
        } catch (Throwable $e) {
            Log::warning('Error extracting text from rich text element: ' . get_class($element), [
                'error' => $e->getMessage()
            ]);
        }
        return $text;
    }
}
