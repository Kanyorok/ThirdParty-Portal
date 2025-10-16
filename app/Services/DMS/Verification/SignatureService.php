<?php

namespace App\Services\DMS\Verification;

use App\Models\DMS\DMSSignature;
use Illuminate\Support\Str;

class SignatureService
{
    public function __construct(public DMSSignature $signature)
    {
    }

    public static function create(string $name)
    {
        /* "Name" => "Approval Green Image"
   "Visibility" => "pub"
   "Opacity" => "90"
   "Horizontal" => "10"
   "Vertical" => "10"
   "Width" => "300"
   "Height" => "500"
   "Content" => "#userid# #datetime#"
   "ContentPosition" => "ss"
   "ContentColour" => "#ff6347"
   "ContentSize" => "28"
   "ContentBorderColour" => "#1b1b1b"
   "ContentBorderWeight" => "1"
   "Description" => null
   "file" => Illuminate\Http\UploadedFile {#5424*/

        $signature = new DMSSignature();
        $signature->fill([
            "SignatureId" => self::_id(),
            //"Name" => , todo continue from here
            "Description", "Visibility", "ImageId", "SignatureHorizontalStart", "SignatureVerticalStart", "SignatureOpacity",
            "SignatureWidth", "SignatureHeight", "Content", "ContentColour", "ContentSize", "ContentPosition", "ContentBorderColour", "ContentBorderWeight",
            'CreatedBy', 'ModifiedBy',
        ])->save();

        return new self($signature);
    }

    protected static function _id(): string
    {
        $number = DMSSignature::query()->withTrashed()->count();
        do {
            $number++;
            $slug = "Sign" . Str::of($number)->padLeft(4, '0');
        } while (DMSSignature::where('SignatureId', $slug)->withTrashed()->exists());

        return $slug;
    }
}
