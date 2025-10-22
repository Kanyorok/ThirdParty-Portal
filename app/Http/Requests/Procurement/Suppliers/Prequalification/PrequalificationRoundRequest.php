<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use App\Enums\Procurement\PrequalificationRoundEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

abstract class PrequalificationRoundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Title' => [$this->isUpdate() ? 'sometimes' : 'required', 'string', 'max:255'],
            'Description' => ['sometimes', 'string', 'nullable'],
            'StartDate' => [$this->isUpdate() ? 'sometimes' : 'required', 'date'],
            'EndDate' => [$this->isUpdate() ? 'sometimes' : 'required', 'date', 'after_or_equal:StartDate'],
            'MaxVendors' => ['sometimes', 'integer', 'min:1', 'nullable'],
            'Status' => ['sometimes', new Enum(PrequalificationRoundEnum::class), 'nullable'],
            'ModifiedBy' => ['sometimes', 'exists:t_Users,Id'],
            'sections' => ['required', 'array', 'min:1', 'bail'],
            'sections.*.section_id' => ['required', 'integer', 'exists:t_Sections,Id'],
            'sections.*.included' => ['nullable', 'boolean'],
            'sections.*.weight' => [
                function ($attribute, $value, $fail) {
                    $sections = $this->input('sections', []);
                    preg_match('/sections\.(\d+)\.weight/', $attribute, $matches);
                    $sectionIndex = $matches[1] ?? null;

                    if ($sectionIndex !== null && !empty($sections[$sectionIndex]['included'])) {
                        if (empty($value) && !is_numeric($value)) {
                            $fail('Weight is required when section is included.');
                            return;
                        }
                        if ((int) $value < 0 || (int) $value > 100) {
                            $fail('Weight must be between 0 and 100.');
                        }
                    }
                },
            ],
            'sections.*.criteria' => ['nullable', 'array'],
            'sections.*.criteria.*.criteria_id' => [
                'required',
                'integer',
                'exists:t_Criterias,Id',
            ],
            'sections.*.criteria.*.included' => ['nullable', 'boolean'],
            'sections.*.criteria.*.weight' => [
                function ($attribute, $value, $fail) {
                    $sections = $this->input('sections', []);
                    preg_match('/sections\.(\d+)\.criteria\.(\d+)\.weight/', $attribute, $matches);
                    $sectionIndex = $matches[1] ?? null;
                    $criteriaIndex = $matches[2] ?? null;

                    if ($sectionIndex !== null && $criteriaIndex !== null && !empty($sections[$sectionIndex]['criteria'][$criteriaIndex]['included'])) {
                        if (!is_numeric($value)) {
                            $fail('Criteria score must be 10.');
                            return;
                        }
                        if ((int)$value !== 10) {
                            $fail('Criteria score is fixed at 10.');
                        }
                    }
                },
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $sections = collect($this->input('sections', []));
            $totalSectionWeight = 0;

            // Only consider sections that are marked as "included"
            $includedSections = $sections->filter(function ($section) {
                return !empty($section['included']);
            });

            if ($includedSections->isEmpty()) {
                $validator->errors()->add('sections', 'At least one section must be included.');
                return;
            }

            foreach ($includedSections as $section) {
                $sectionWeight = (int) ($section['weight'] ?? 0);
                $totalSectionWeight += $sectionWeight;
            }

            // Validate that the total weight of all sections is 100
            if ($totalSectionWeight !== 100) {
                $validator->errors()->add('sections', "The total weight of all included sections must equal 100. Current total: {$totalSectionWeight}.");
            }
        });
    }

    protected function isUpdate(): bool
    {
        return $this->method() === 'PUT';
    }
}
