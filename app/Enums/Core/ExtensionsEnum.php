<?php

namespace App\Enums\Core;

use App\Exceptions\ErroredException;
use App\Traits\UsefulEnumTrait;

enum ExtensionsEnum: string
{
    use UsefulEnumTrait;

    case Jpeg = 'jpeg';
    case Png = 'png';
    case Gif = 'gif';
    case Bmp = 'bmp';
    case Svg = 'svg';
    case Mp4 = 'mp4';
    case Mpeg = 'mpeg';
    case Webm = 'webm';
    case AVI = 'avi';
    case Docx = 'docx';
    case Doc = 'doc';
    case Pdf = 'pdf';
    case Xls = 'xls';
    case Xlsx = 'xlsx';
    case PPt = 'ppt';
    case Odp = 'odp';
    case Pptx = 'pptx';
    case Txt = 'txt';
    case Csv = 'csv';
    case ICS = 'ics';
    case Json = 'json';
    case Rar = 'rar';
    case RTF = 'rtf';
    case Zip = 'zip';
    case SevenZ = '7z';
    case Ods = 'ods';
    case Odt = 'odt';
    case None = 'n';

    public static function getAllMimeTypes(): array
    {
        $data = collect();
        foreach (self::getAll() as $value) {
            if ($value->value === self::None->value) {
                continue;
            }
            $data->add($value->getMimeType());
        }
        return $data->toArray();
    }

    public function getMimeType(): string
    {
        return match ($this) {
            self::Jpeg => 'image/jpeg',
            self::Png => 'image/png',
            self::Gif => 'image/gif',
            self::Bmp => 'image/bmp',
            self::Svg => 'image/svg+xml',
            self::Mp4 => 'video/mp4',
            self::AVI => 'video/x-msvideo',
            self::Mpeg => 'video/mpeg',
            self::Webm => 'video/webm',
            self::Doc => 'application/msword',
            self::Docx => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            self::Odt => 'application/vnd.oasis.opendocument.text',
            self::Pdf => 'application/pdf',
            self::Xls => 'application/vnd.ms-excel',
            self::Xlsx => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::Ods => 'application/vnd.oasis.opendocument.spreadsheet',
            self::PPt => 'application/vnd.ms-powerpoint',
            self::Pptx => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            self::Odp => 'application/vnd.oasis.opendocument.presentation',
            self::Txt => 'text/plain',
            self::Csv => 'text/csv',
            self::Rar => 'application/vnd.rar',
            self::ICS => 'text/calendar',
            self::Json => 'application/json',
            self::Zip => 'application/zip',
            self::RTF => 'application/rtf',
            self::SevenZ => 'application/x-7z-compressed',
            self::None => ''
        };
    }

    public function isPreview(): bool
    {
        return ($this->isImage() || $this->isVideo() || ($this->value === self::Pdf->value));
    }

    /**
     * @throws ErroredException
     */
    public static function fromMimeType(string $MimeType): self
    {
        foreach (self::getAll() as $value) {
            if ($value->getMimeType() === $MimeType) {
                return $value;
            }
        }
        throw new ErroredException('Unknown mime type');
    }

    public function getIcon(string $type = 'fa'): string
    {
        if ($type === 'img') {
            return $this->images();
        }
        return $this->_fa();
    }

    private function _fa(): string
    {
        return match ($this) {
            self::Jpeg, self::Png, self::Gif, self::Bmp, self::Svg => '<i class="fa-regular fa-image"></i>',
            self::Mp4, self::Webm, self::AVI, self::Mpeg => '<i class="fa-regular fa-file-video"></i>',
            self::Doc, self::Docx, self::RTF, self::Odt => '<i class="fa-regular fa-file-word"></i>',
            self::Pdf => '<i class="fa-regular fa-file-pdf"></i>',
            self::Csv, self::Xls, self::Xlsx, self::Ods => '<i class="fa-regular fa-file-excel"></i>',
            self::PPt, self::Pptx, self::Odp => '<i class="fa-regular fa-file-powerpoint"></i>',
            self::Txt => '<i class="fa-regular fa-file-lines"></i>',
            self::ICS => '<i class="fa-regular fa-calendar-alt"></i>',
            // => '<i class="fa-regular fa-file-csv"></i>',
            self::Rar, self::Zip, self::SevenZ => '<i class="fa-regular fa-file-archive"></i>',
            self::Json => '<i class="fa-regular fa-file-code"></i>',
            self::None => '?',
        };
    }

    private function images(): string
    {//source https://dryicons.com/free-icons/file-calendar
        return match ($this) {
            self::Jpeg, self::Png, self::Gif, self::Bmp, self::Svg => asset('assets/img/files/img-file-img.svg'),
            self::Mp4, self::Webm, self::AVI, self::Mpeg => asset('assets/img/files/img-file-video.svg'),
            self::Doc, self::Docx, self::RTF, self::Odt => asset('assets/img/files/img-file-doc.svg'),
            self::Pdf => asset('assets/img/files/img-file-pdf.svg'),
            self::Csv, self::Xls, self::Xlsx, self::Ods => asset('assets/img/files/img-file-xls.svg'),
            self::PPt, self::Pptx, self::Odp => asset('assets/img/files/img-file-ppt.svg'),
            self::Txt => asset('assets/img/files/img-file-txt.svg'),
            self::ICS => asset('assets/img/files/img-file-cal.svg'),
            self::Rar, => asset('assets/img/files/img-file-rar.svg'),
            self::Zip, self::SevenZ => asset('assets/img/files/img-file-zip.svg'),
            self::Json => asset('assets/img/files/img-file-code.svg'),
            self::None => asset('assets/img/files/img-file-blank.svg'),
        };
    }

    public static function getVideos($type = 'mimetype'): array
    {
        $videos = collect([self::Mp4, self::Webm, self::AVI, self::Mpeg]);
        if ($type === 'name') {
            return $videos->map(function ($item) {
                return $item->name;
            })->toArray();
        }
        if (in_array($type, ['value', 'ext', 'extension'], true)) {
            return $videos->map(function ($item) {
                return $item->value;
            })->toArray();
        }

        return $videos->map(function ($item) {
            return $item->getMimeType();
        })->toArray();
    }

    public function isImage(): bool
    {
        return in_array($this->value, [self::Jpeg->value, self::Png->value, self::Gif->value, self::Bmp->value, self::Svg->value], true);
    }

    public function isVideo(): bool
    {
        return in_array($this->value, self::getVideos('value'), true);
    }
}
