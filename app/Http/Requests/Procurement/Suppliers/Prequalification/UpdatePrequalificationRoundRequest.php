<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

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
