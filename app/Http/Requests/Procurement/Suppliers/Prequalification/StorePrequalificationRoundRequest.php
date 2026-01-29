<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

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
