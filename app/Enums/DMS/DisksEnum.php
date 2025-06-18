<?php

namespace App\Enums\DMS;

use App\Exceptions\ErroredException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

enum DisksEnum: string
{
    case Local = 'fs';

    case S3 = 's3';

    case Database = 'db';

    /**
     * @throws ErroredException
     */
    public function path(): string
    {
        if ($this->value === self::Local->value) {
            $path = date('Y') . '/' . date('m');
            if (!Storage::disk($this->value)->exists($path) && !Storage::disk($this->value)->makeDirectory($path)) {
                throw new ErroredException('Could not create directory');
            }

            return $path;
        }
        throw new ErroredException('Could not create directory');
    }
}
