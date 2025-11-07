<?php

namespace App\Services;

use App\Models\Core\Locality;

class LocalityService
{


    public function __construct(public Locality $locality)
    {
    }

    public function getLocation(bool $array = false): string|array
    {
        if ($this->locality->in instanceof Locality) {
            return ($array) ? [
                'ID' => $this->locality->ID,
                'Name' => $this->locality->Name . ' - ' . $this->locality->in?->Name,
            ] : $this->locality->Name . ' - ' . $this->locality->in?->Name;
        }

        return ($array) ? [
            'ID' => $this->locality->ID,
            'Name' => $this->locality->Name,
        ] : $this->locality->Name;
    }
}
