<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use App\Http\Requests\Procurement\Suppliers\Prequalification\PrequalificationRoundRequest;

class StorePrequalificationRoundRequest extends PrequalificationRoundRequest
{
    /**
     * @return bool
     */

    protected function isUpdate(): bool
    {
        return false;
    }
}
