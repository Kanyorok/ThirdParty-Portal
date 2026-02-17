<?php

namespace App\Http\Requests\Procurement\Tenders;

use App\Http\Requests\ApiRequest;

class AddTenderSupplierRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'supplier_id' => 'required|integer|exists:t_Suppliers,Id',
        ];
    }
}
