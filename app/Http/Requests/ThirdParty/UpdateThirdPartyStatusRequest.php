<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ThirdPartyStatusEnum;

class UpdateThirdPartyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ThirdPartyStatusEnum::class)],
        ];
    }
}
