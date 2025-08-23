<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use App\Http\Requests\Procurement\Suppliers\Prequalification\PrequalificationRoundRequest;

class UpdatePrequalificationRoundRequest extends PrequalificationRoundRequest
{
    /**
     * return bool
     */

    protected function isUpdate(): bool
    {
        return true;
    }
}
