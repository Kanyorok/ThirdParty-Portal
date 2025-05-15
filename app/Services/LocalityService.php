<?php

namespace App\Services;

use App\Enums\LocalityTypeEnum;
use App\Models\Core\Locality;

class LocalityService
{
    public LocalityTypeEnum $typeEnum;

    public function __construct(public Locality $locality)
    {
        $this->typeEnum = $locality->LocationType;
    }

    public function getLocation(): string
    {
        if (is_null($this->locality->LocalityID)) {
            return $this->locality->Name;
        }

        $parent = $this->locality->in;
        if (!$parent instanceof Locality) {
            return $this->locality->Name;
        }

        return $this->locality->Name . ', ' . $parent->Name;
    }
}
