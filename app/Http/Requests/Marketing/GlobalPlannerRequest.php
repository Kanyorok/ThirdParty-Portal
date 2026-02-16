<?php

namespace App\Http\Requests\Marketing;

use App\Enums\Marketing\PlannerTypeEnum;
use App\Models\CRM\MarketingPlanner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class GlobalPlannerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Name' => [
                              'required',
                              'string',
                              'max:200',
                             ],
                'plans' => [
                              'required',
                              'array',
                              'min:1',
                              'max:500',
                             ],
                'plans.*' => ['required'],
                'Notes' => [
                              'nullable',
                              'string',
                             ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getPlans(): Collection
    {
        $plans = collect();
        $id = 1;
        foreach ($this->validated('plans') as $planId) {
            $plan = MarketingPlanner::query()->where('PlannerID', $planId)->where('Type', PlannerTypeEnum::BranchPlanner->value)->first();
            if ($plan instanceof MarketingPlanner) {
                $plans->push($plan->Id);
                $id++;

                continue;
            }

            throw ValidationException::withMessages([
                                                     'plans' => 'plan ' . $id . ' is not valid',
                                                    ]);
        }

        return $plans;
    }
}
