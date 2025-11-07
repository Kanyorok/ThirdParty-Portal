<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class MedicalFundContributorRequest extends FormRequest
{
    /**
     * Authorize all users for now.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sanitize and prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up package_ids: remove blanks and cast to integers
        $this->merge([
            'package_ids' => collect($this->package_ids ?? [])
                ->filter(fn($v) => is_numeric($v))
                ->map(fn($v) => (int)$v)
                ->values()
                ->all(),
        ]);
    }

    /**
     * Define validation rules.
     */
    public function rules(): array
    {
        // Determine if we are updating or creating
        $isUpdate = $this->route('contributor') !== null;

        return [
            'ThirdPartyId'  => ['required', 'exists:t_ThirdParties,Id'],
            'EffectiveFrom' => ['nullable', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after_or_equal:EffectiveFrom'],
            'Status'        => ['required', 'exists:t_CodeDetails,ID'],
            'PartyId'       => ['nullable', 'integer'],
            'package_ids'   => ['nullable', 'array'],
            'package_ids.*' => ['integer'], // Now guaranteed to be integers by prepareForValidation()
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'ThirdPartyId.required'      => 'Please select a third party.',
            'ThirdPartyId.exists'        => 'The selected third party does not exist.',
            'EffectiveTo.after_or_equal' => 'The end date must be after or equal to the start date.',
            'Status.required'            => 'Please select a valid contributor status.',
            'Status.exists'              => 'The selected status is invalid.',
            'package_ids.array'          => 'Invalid package data format.',
            'package_ids.*.integer'      => 'Each selected package must be a valid ID.',
        ];
    }
}
