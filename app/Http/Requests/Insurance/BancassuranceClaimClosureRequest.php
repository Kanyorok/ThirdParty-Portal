<?php

namespace App\Http\Requests\Insurance;

use App\Enums\Insurance\InsuranceClosureEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BancassuranceClaimClosureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ClaimId' => 'required|exists:t_BancassuranceClaims,Id',
            'FinalStatus' => ['required', new Enum(InsuranceClosureEnum::class)],
            'FinalRemarks' => 'nullable|string',
            'ClosureDate' => 'required|date',
        ];
    }
}
