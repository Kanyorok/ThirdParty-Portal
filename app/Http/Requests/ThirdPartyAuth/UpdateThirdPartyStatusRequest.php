<?php

namespace App\Http\Requests\ThirdPartyAuth;

use App\Enums\ThirdParty\ThirdPartyStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThirdPartyStatusRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ThirdPartyStatusEnum::class)],
        ];
    }
}
