<?php

namespace App\Http\Requests\ProductDev;

use App\Exceptions\ErroredException;
use App\Services\StaticListsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class UpdateProductDevelopmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Name' => [
                                     'sometimes',
                                     'required',
                                     'string',
                                     'max:255',
                                    ],
                'TargetGroup' => [
                                     'sometimes',
                                     'required',
                                     'string',
                                     'max:255',
                                    ],
                'Notes' => [
                                     'sometimes',
                                     'required',
                                     'string',
                                     'max:5000',
                                    ],
                'Regulatory' => [
                                     'sometimes',
                                     'required',
                                     'string',
                                     'max:5000',
                                    ],
                'Justification' => [
                                     'sometimes',
                                     'required',
                                     'string',
                                     'max:5000',
                                    ],
                'Risks' => [
                                     'sometimes',
                                     'required',
                                     'string',
                                     'max:5000',
                                    ],
                'RiskStrategies' => [
                                     'sometimes',
                                     'required',
                                     'string',
                                     'max:5000',
                                    ],
                'User_ID' => [
                                     'sometimes',
                                     'required',
                                     Rule::exists('t_Users', 'UserID'),
                                    ],
                'StageId' => [
                                     'sometimes',
                                     'required',
                    Rule::exists('t_CodeDetails', 'ID')->where(function (Builder $query) {
                        return $query->where('CodeID', StaticListsService::ProductDevelopmentStages);
                    }),
                                    ],
                'Income' => [
                    'sometimes',
                    'required',
                    'numeric',
                                    ],
                'Revenue' => [
                    'sometimes',
                    'required',
                    'numeric',
                                    ],
               ];
    }

    /**
     * @throws ErroredException
     */
    public function getData(): Collection
    {
        $data = collect();
        foreach (['Name', 'TargetGroup', 'User_ID', 'Income', 'Revenue', 'Regulatory', 'Notes', 'Justification', 'Risks', 'RiskStrategies', 'StageId'] as $field) {
            if ($this->has($field)) {
                $data = $data->merge([$field => $this->cleanField($field)]);
            }
        }

        if ($data->isEmpty()) {
            throw new ErroredException('No parameter for update found');
        }

        return $data;
    }

    protected function cleanField(mixed $field): mixed
    {
        if (in_array($field, ['Income', 'Revenue'])) {
            return (float) $this->validated($field);
        }

        return str_replace(PHP_EOL, '', $this->validated($field));
    }
}
