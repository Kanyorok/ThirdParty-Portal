<?php

namespace App\Http\Requests\Marketing;

use App\Enums\Core\ComparisonOperatorsEnum;
use App\Models\MarketingList;
use App\Models\SysFilter;
use App\Services\Marketing\MarketingFilterService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListFilterRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter' => 'required',
            'Start' => ['nullable', 'string'],
            'End' => ['nullable', 'string'],
            'Value' => ['nullable', 'string'],
            'Values' => ['nullable', 'array', 'min:1', 'max:10'],
            'operation' => ['required', Rule::in(['and', 'or'])]
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getValue(SysFilter $filter): string|array
    {

        if ($filter->Operator->value === ComparisonOperatorsEnum::In->value) {
            $values = $this->validated('Values');
            if (!is_array($values)) {
                throw ValidationException::withMessages([
                    'Values' => 'select one at least one value'
                ]);
            }

            $valid = collect();
            foreach ($values as $value) {
                if (MarketingFilterService::sourceValue($filter, $value)) {
                    $valid->add($value);
                }
            }

            if ($valid->isEmpty()) {
                throw ValidationException::withMessages([
                    'Values' => 'select one at least one value'
                ]);
            }

            return $valid->toArray();
        }

        if ($filter->Operator->value === ComparisonOperatorsEnum::Between->value) {
            $start = $this->validated('Start');
            $end = $this->validated('End');

            if (!$filter->DataType->isValid($start)) {
                throw ValidationException::withMessages([
                    'Start' => 'invalid format of ' . $filter->DataType->name
                ]);
            }
            if (!$filter->DataType->isValid($end)) {
                throw ValidationException::withMessages([
                    'End' => 'invalid format of ' . $filter->DataType->name
                ]);
            }
            return [$start, $end];
        }

        if ($filter->Operator->isBasic()) {
            if ($filter->DataType->isValid($this->validated('Value'))) {
                return $this->validated('Value');
            }
            throw ValidationException::withMessages([
                'Value' => 'invalid format of ' . $filter->DataType->name
            ]);
        }

        throw ValidationException::withMessages([
            'filter' => 'issue with type in filter'
        ]);
    }


    /**
     * @throws ValidationException
     */
    public function getFilter(MarketingList $list): SysFilter
    {
        $filter = SysFilter::query()->where('Source', $list->Source)->where('Id', $this->validated('filter'))->first();
        if ($filter instanceof SysFilter) {
            return $filter;
        }

        throw ValidationException::withMessages([
            'filter' => 'unknown filter given'
        ]);
    }
}
