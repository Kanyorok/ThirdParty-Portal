<?php

namespace App\Enums\DMS;

enum DisksEnum: string
{
    case Local = 'fs';

    case S3 = 's3';

    case Database = 'db';
}
