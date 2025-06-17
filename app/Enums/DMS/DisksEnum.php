<?php

namespace App\Enums\DMS;

use App\Exceptions\ErroredException;
use Illuminate\Support\Facades\File;

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
            $path = storage_path('app/files/' . date('Y') . '/' . date('m'));
            if (!File::exists($path)) {
                if (!File::makeDirectory($path, 0777, true)) {
                    throw new ErroredException('Could not create directory');
                }
            }
            return $path;
        }
        throw new ErroredException('Could not create directory');
    }
}
