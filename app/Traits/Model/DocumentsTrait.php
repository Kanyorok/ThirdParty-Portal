<?php

namespace App\Traits\Model;

use App\Models\Communication\SMS;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait DocumentsTrait
{
    public function documents(): MorphMany
    {
        return $this->morphMany(SMS::class, 'related', "Related", "RelatedID", 'Id');
    }

    public function newDocument()
     {
         //todo fix this
     }
}
