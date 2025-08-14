<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Procurement\PrequalificationRoundEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrequalificationRoundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Title'       => ['sometimes', 'string', 'max:255'],
            'Description' => ['sometimes', 'string'],
            'StartDate'   => ['sometimes', 'date'],
            'EndDate'     => ['sometimes', 'date', 'after_or_equal:StartDate'],
            'MaxVendors'  => ['sometimes', 'integer', 'min:1'],
            'Status'      => ['sometimes', new Enum(PrequalificationRoundEnum::class)],
            'ModifiedBy'  => ['sometimes', 'exists:t_Users,Id'],

            // Sections array
            'sections'                     => ['required', 'array', 'min:1'],
            'sections.*.section_id'        => ['required', 'integer', 'exists:t_Sections,id'],
            'sections.*.included'          => ['nullable', 'boolean'],
            'sections.*.weight'            => [
                Rule::requiredIf(fn() => $this->isSectionIncluded()),
                'integer',
                'min:0',
                'max:100'
            ],
            'sections.*.criteria_ids'      => [
                Rule::requiredIf(fn() => $this->isSectionIncluded()),
                'array',
                'min:1'
            ],
            'sections.*.criteria_ids.*' => [
                Rule::requiredIf(fn() => $this->isSectionIncluded()),
                'integer',
                'exists:t_Criterias,id'
            ],
        ];
    }

    private function isSectionIncluded(): bool
    {
        foreach ($this->sections ?? [] as $section) {
            if (!empty($section['included'])) {
                return true;
            }
        }
        return false;
    }
}
