<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Models\Procurement\Section;
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
                    // Extract section index from attribute path
                    preg_match('/sections\.(\d+)\.weight/', $attribute, $matches);
                    $sectionIndex = $matches[1] ?? null;

                    if ($sectionIndex !== null) {
                        $sections = $this->input('sections', []);
                        $isIncluded = !empty($sections[$sectionIndex]['included']);

                        if ($isIncluded) {
                            if (empty($value) || !is_numeric($value)) {
                                $fail('Weight is required when section is included.');
                                return;
                            }

                            $weight = (int) $value;
                            if ($weight < 0 || $weight > 100) {
                                $fail('Weight must be between 0 and 100.');
                            }
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
                    // Extract section and criteria indices from attribute path
                    preg_match('/sections\.(\d+)\.criteria\.(\d+)\.weight/', $attribute, $matches);
                    $sectionIndex = $matches[1] ?? null;
                    $criteriaIndex = $matches[2] ?? null;

                    if ($sectionIndex !== null && $criteriaIndex !== null) {
                        $sections = $this->input('sections', []);
                        $isIncluded = !empty($sections[$sectionIndex]['criteria'][$criteriaIndex]['included']);

                        if ($isIncluded) {
                            if (empty($value) || !is_numeric($value)) {
                                $fail('Weight is required when criteria is included.');
                                return;
                            }

                            $weight = (int) $value;
                            if ($weight < 0 || $weight > 100) {
                                $fail('Weight must be between 0 and 100.');
                            }
                        }
                    }
                },
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $sections = $this->input('sections', []);
            $totalSectionWeight = 0;
            $hasIncludedSections = false;

            foreach ($sections as $sectionIndex => $section) {
                // Skip if section is not included
                if (empty($section['included'])) {
                    continue;
                }

                $hasIncludedSections = true;
                $sectionWeight = (int) ($section['weight'] ?? 0);
                $totalSectionWeight += $sectionWeight;
                $totalCriteriaWeight = 0;

                // Check if section has criteria
                if (isset($section['criteria']) && is_array($section['criteria'])) {
                    $hasIncludedCriteria = false;

                    foreach ($section['criteria'] as $criteriaIndex => $criteria) {
                        if (!empty($criteria['included'])) {
                            $hasIncludedCriteria = true;
                            $criteriaWeight = (int) ($criteria['weight'] ?? 0);
                            $totalCriteriaWeight += $criteriaWeight;
                        }
                    }

                    // Only validate criteria weights if there are included criteria
                    if ($hasIncludedCriteria && $totalCriteriaWeight !== $sectionWeight) {
                        $sectionModel = Section::find($section['section_id']);
                        $sectionName = $sectionModel ? $sectionModel->SectionName : 'Unknown Section';
                        $validator->errors()->add('sections', "The total weight of criteria for section '{$sectionName}' must equal its section weight of {$sectionWeight}%. Current total: {$totalCriteriaWeight}%");
                    }
                } else {
                    if ($sectionWeight > 0) {
                        $sectionModel = Section::find($section['section_id']);
                        $sectionName = $sectionModel ? $sectionModel->SectionName : 'Unknown Section';
                        $validator->errors()->add('sections', "Section '{$sectionName}' has weight but no criteria selected.");
                    }
                }
            }

            // Only check total section weight if there are included sections
            if ($hasIncludedSections && $totalSectionWeight !== 100) {
                $validator->errors()->add('sections', "The total weight of all included sections must equal 100%. Current total: {$totalSectionWeight}%");
            }

            // Check if at least one section is included
            if (!$hasIncludedSections) {
                $validator->errors()->add('sections', 'At least one section must be included.');
            }
        });
    }

    protected function isUpdate(): bool
    {
        return $this->method() === 'PUT';
    }
}
