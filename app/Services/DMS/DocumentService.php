<?php

namespace App\Services\DMS;

use App\Models\DMS\Document;

class DocumentService extends PermissionsService
{
    public function __construct(public Document $document)
    {
    }


}
