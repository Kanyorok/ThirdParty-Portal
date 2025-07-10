<?php

namespace App\Services\DMS;

use App\Enums\Core\DataTypesEnum;
use App\Enums\Core\ExtensionsEnum;
use App\Helpers\SystemHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

// Add getID3 import for media file analysis
use getID3;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpPresentation\IOFactory as PptFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\IOFactory as WordFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileProperties
{
    public const TIME_FORMAT = 'H:i:s';
    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s T';

    protected Collection $properties;

    public function __construct(protected UploadedFile $file, protected ExtensionsEnum $type)
    {
        $this->properties = collect();
        $this->extractBasicProperties()
            ->extractTypeSpecificProperties();
    }

    /**
     * Extract file-type-specific properties
     */
    private function extractTypeSpecificProperties(): void
    {
        try {
            if ($this->type->isImage()) {
                $this->extractImageProperties();
                return;
            }

            if ($this->type->isVideo()) {
                $this->extractVideoProperties();
                return;
            }

            if ($this->type->isAudio()) {
                $this->extractAudioProperties();
                return;
            }

            if ($this->type->isDocument()) {
                $this->extractDocumentProperties();
                return;
            }
            if ($this->type->isPresentation()) {
                $this->extractPresentationProperties();
                return;
            }

            if ($this->type->value === ExtensionsEnum::Csv->value || $this->type->isSpreadsheet()) {
                $this->extractSpreadsheetProperties();
                return;
            }

            if ($this->type->value === ExtensionsEnum::Pdf->value) {
                $this->extractPdfProperties();
                return;
            }

        } catch (Exception $e) {
            Log::warning('Failed to extract type-specific properties: ' . $e->getMessage());
        }
    }

    /**
     * Extract image-specific properties
     */
    private function extractImageProperties(): void
    {
        // Basic image info
        $imageInfo = getimagesize($this->file->getRealPath());
        if ($imageInfo) {
            if (is_int($imageInfo[0])) {
                $this->add('width', $imageInfo[0], DataTypesEnum::Integer);
            }
            if (is_int($imageInfo[1])) {
                $this->add('height', $imageInfo[1], DataTypesEnum::Integer);
            }
            if (is_int($imageInfo[2])) {
                $this->add('image_type', image_type_to_mime_type($imageInfo[2]));
            }
            /*  $properties['bits'] = $imageInfo['bits'] ?? null;
              $properties['channels'] = $imageInfo['channels'] ?? null;*/
        }

        // EXIF data for JPEG images
        if (extension_loaded('exif') && in_array(strtolower($this->file->getClientOriginalExtension()), ['jpg', 'jpeg'])) {
            try {
                $exifData = exif_read_data($this->file->getRealPath());
                if ($exifData) {
                    if (isset($exifData['DateTime'])) {
                        try {
                            $this->add('date_taken', Carbon::parse($exifData['DateTime'])->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
                        } catch (Exception) {
                        }
                    }
                    if (isset($exifData['Make'])) {
                        $this->add('camera_make', $exifData['Make']);
                    }
                    if (isset($exifData['Model'])) {
                        $this->add('camera_model', $exifData['Model']);
                    }
                    if (isset($exifData['Orientation'])) {
                        $this->add('orientation', $exifData['Orientation']);
                    }
                    if (isset($exifData['Flash'])) {
                        $this->add('flash', $exifData['Flash']);
                    }
                    if (isset($exifData['FocalLength'])) {
                        $this->add('focal_length', $exifData['FocalLength']);
                    }
                    if (isset($exifData['ISOSpeedRatings'])) {
                        $this->add('iso', $exifData['ISOSpeedRatings']);
                    }
                }
            } catch (Exception $e) {
                Log::warning('Failed to extract EXIF data: ' . $e->getMessage());
            }
        }
    }

    private function add(string $name, mixed $value, DataTypesEnum $type = DataTypesEnum::String): self
    {
        $this->properties->add([
            'Name' => $name,
            'Value' => $value,
            'DataType' => $type->value,
        ]);
        return $this;
    }

    /**
     * Extract video properties using getID3 (if available)
     */
    private function extractVideoProperties(): void
    {
        if (class_exists(getID3::class)) {
            try {
                $getID3 = new getID3;
                $fileInfo = $getID3->analyze($this->file->getRealPath());

                if (isset($fileInfo['playtime_seconds'])) {
                    $this->add('duration', $fileInfo['playtime_seconds'], DataTypesEnum::Float);
                    $this->add('duration_formatted', gmdate(self::TIME_FORMAT, $fileInfo['playtime_seconds']), DataTypesEnum::Time);
                }

                if (isset($fileInfo['video'])) {
                    if (isset($fileInfo['video']['resolution_x'])) {
                        $this->add('video_width', $fileInfo['video']['resolution_x'], DataTypesEnum::Integer);
                    }
                    if (isset($fileInfo['video']['resolution_y'])) {
                        $this->add('video_height', $fileInfo['video']['resolution_y'], DataTypesEnum::Integer);
                    }
                    if (isset($fileInfo['video']['codec'])) {
                        $this->add('video_codec', $fileInfo['video']['codec']);
                    }
                    if (isset($fileInfo['video']['bitrate'])) {
                        $this->add('video_bitrate', $fileInfo['video']['bitrate'], DataTypesEnum::Integer);
                    }
                    if (isset($fileInfo['video']['frame_rate'])) {
                        $this->add('video_frame_rate', $fileInfo['video']['frame_rate'], DataTypesEnum::Float);
                    }
                }

                if (isset($fileInfo['audio'])) {
                    if (isset($fileInfo['audio']['codec'])) {
                        $this->add('audio_codec', $fileInfo['audio']['codec']);
                    }
                    if (isset($fileInfo['audio']['bitrate'])) {
                        $this->add('audio_bitrate', $fileInfo['audio']['bitrate'], DataTypesEnum::Integer);
                    }
                    if (isset($fileInfo['audio']['channels'])) {
                        $this->add('audio_channels', $fileInfo['audio']['channels'], DataTypesEnum::Integer);
                    }
                    if (isset($fileInfo['audio']['sample_rate'])) {
                        $this->add('audio_sample_rate', $fileInfo['audio']['sample_rate'], DataTypesEnum::Integer);
                    }
                }

            } catch (Exception $e) {
                Log::warning('Failed to extract video properties: ' . $e->getMessage());
            }
        }

    }

    /**
     * Extract audio properties
     */
    private function extractAudioProperties(): void
    {
        $this->extractVideoProperties();
    }

    /**
     * Extract Office document properties
     */
    private function extractDocumentProperties(): void
    {
        try {
            $reader = $this->type->getDocumentType();
            if (empty($reader)) {
                return;
            }
            $document = WordFactory::load($this->file->getRealPath(), $reader);
            $properties = $document->getDocInfo();

            if ($creator = $properties->getCreator()) {
                $this->add('creator', $creator);
            }
            if ($company = $properties->getCompany()) {
                $this->add('company', $company);
            }
            if ($title = $properties->getTitle()) {
                $this->add('title', $title);
            }
            if ($description = $properties->getDescription()) {
                $this->add('description', $description);
            }
            if ($lastModifiedBy = $properties->getLastModifiedBy()) {
                $this->add('modified_by', $lastModifiedBy);
            }
            if ($created = $properties->getCreated()) {
                try {
                    $this->add('creation_date', Carbon::createFromFormat('U', $created)?->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
                } catch (Exception $e) {
                }
            }
            if ($modified = $properties->getModified()) {
                try {
                    $this->add('modified_date', Carbon::createFromFormat('U', $modified)?->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
                } catch (Exception $e) {
                }
            }
            /* if ($words = $properties->getWords()) {
                 $this->add('word_count', $words, DataTypesEnum::Integer);
             }
             if ($pages = $properties->getPages()) {
                 $this->add('page_count', $pages, DataTypesEnum::Integer);
             }*/
        } catch (Exception $e) {
            Log::warning('Failed to extract document properties: ' . $e->getMessage());
        }
    }

    private function extractPresentationProperties(): void
    {
        try {
            $presentation = PptFactory::load($this->file->getRealPath());
            $properties = $presentation->getDocumentProperties();

            if ($creator = $properties->getCreator()) {
                $this->add('creator', $creator);
            }
            if ($lastModifiedBy = $properties->getLastModifiedBy()) {
                $this->add('modified_by', $lastModifiedBy);
            }
            if ($title = $properties->getTitle()) {
                $this->add('title', $title);
            }
            if ($description = $properties->getDescription()) {
                $this->add('description', $description);
            }
            if ($created = $properties->getCreated()) {
                try {
                    $this->add('creation_date', Carbon::createFromFormat('U', $created)?->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
                } catch (Exception $e) {
                }
            }
            if ($modified = $properties->getModified()) {
                try {
                    $this->add('modified_date', Carbon::createFromFormat('U', $modified)?->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
                } catch (Exception $e) {
                }
            }

            $this->add('slide_count', $presentation->getSlideCount(), DataTypesEnum::Integer);
        } catch (Exception $e) {
            Log::warning('Failed to extract presentation properties: ' . $e->getMessage());
        }
    }

    private function extractSpreadsheetProperties(): void
    {
        try {
            $spreadsheet = IOFactory::load($this->file->getRealPath());
            $properties = $spreadsheet->getProperties();

            if (!empty(trim($properties->getCreator()))) {
                $this->add('creator', $properties->getCreator());
            }
            if (!empty(trim($properties->getLastModifiedBy()))) {
                $this->add('modified_by', $properties->getLastModifiedBy());
            }
            if ($created = $properties->getCreated()) {
                try {
                    $this->add('creation_date', Carbon::createFromFormat('U', (integer)$created)?->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
                } catch (Exception $e) {
                }
            }

            if ($modified = $properties->getModified()) {
                $this->add('modified_date', Carbon::createFromFormat('U', (integer)$modified)?->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
            }

            $this->add('sheet_count', $spreadsheet->getSheetCount(), DataTypesEnum::Integer);
            $this->add('sheet_names', implode(', ', $spreadsheet->getSheetNames()));
        } catch (Exception $e) {
            Log::warning('Failed to extract spreadsheet properties: ' . $e->getMessage());
        }

    }

    public function getProperties(): Collection
    {
        return $this->properties;
    }

    /**
     * Extract PDF properties
     */
    private function extractPdfProperties(): void
    {
        try {
            if (shell_exec("command -v pdfinfo") === null) {
                SystemHelper::notifyAdmin('pdfinfo command not found. Please install poppler-utils package.');
                return;
            }

            $pdfinfo = shell_exec("pdfinfo " . $this->file->getRealPath());
            if ($pdfinfo === null) {
                Log::warning('Failed to get PDF information');
                return;
            }

            foreach (Str::of($pdfinfo)->trim()->explode("\n") as $line) {
                // Split on the first colon to separate key and value
                $colonPos = strpos($line, ':');
                if ($colonPos === false) {
                    continue;
                }

                $key = trim(substr($line, 0, $colonPos));
                $value = trim(substr($line, $colonPos + 1));

                if (empty($value)) {
                    continue;
                }

                // Convert numeric values to appropriate types
                if (is_numeric($value)) {
                    $this->add($key, (float)$value, (str_contains($value, '.')) ? DataTypesEnum::Integer : DataTypesEnum::Float);
                    continue;
                }

                if ($value === 'yes' || $value === 'no') {
                    $this->add($key, (int)($value === 'yes'), DataTypesEnum::Boolean);
                    continue;
                }

                if (Str::of($key)->contains('Date', true)) {
                    try {
                        $this->add($key, Carbon::createFromFormat('D M  j H:i:s Y T', $value)?->timezone(config('app.timezone'))->format(self::DATE_TIME_FORMAT), DataTypesEnum::DateTime);
                    } catch (Exception $e) {
                    }
                    continue;
                }

                $this->add($key, $value);
            }
        } catch (Exception $e) {
            Log::warning('Failed to extract PDF properties: ' . $e->getMessage());
        }

    }

    /**
     * Extract basic file properties using Symfony PropertyInfo
     */
    private function extractBasicProperties(): static
    {
        return $this->add('original_name', $this->file->getClientOriginalName())
            ->add('mime_type', $this->file->getMimeType() ?? $this->file->getClientMimeType())
            ->add('size', $this->file->getSize(), DataTypesEnum::Integer)
            ->add('extension', $this->type->value);
    }


}
