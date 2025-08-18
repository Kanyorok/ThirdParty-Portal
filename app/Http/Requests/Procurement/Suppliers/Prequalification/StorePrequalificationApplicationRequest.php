<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use App\Enums\ThirdPartyTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StorePrequalificationApplicationRequest extends FormRequest
{

    public function authorize(): bool
    {
        // return $this->user() && $this->user()->type === ThirdPartyTypeEnum::Supplier;
        return true;
    }

    public function rules(): array
    {
        return [
            'round_id' => ['required', 'integer', 'exists:t_PrequalificationRounds,RoundID'],
            'responses' => ['required', 'array'],
            'responses' => ['nullable', 'array'],
            'responses.*.criteria_id' => ['required_with:responses', 'integer', 'exists:t_PrequalificationCriterias'],
        ];
    }
}
