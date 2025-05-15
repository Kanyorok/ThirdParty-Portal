<?php

namespace App\Http\Requests\Marketing;

use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use App\Services\StaticListsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PlannerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Name'   => [
                             'required',
                             'string',
                             'max:200',
                            ],
                'Branch' => [
                             'required',
                             'string',
                            ],
                'Mode'   => [
                             'required',
                             'string',
                            ],
                'Notes'  => [
                             'nullable',
                             'string',
                            ],
               ];
    }


    /**
     * @throws ValidationException
     */
    public function getMode(): CodeDetail
    {
        $Mode = StaticListsService::getRawList(StaticListsService::MarketingModes)->where('ID', $this->validated('Mode'))->first();
        if ($Mode instanceof CodeDetail) {
            return $Mode;
        }
        throw ValidationException::withMessages(['Mode' => 'Invalid Marketing Modes.']);
    }

    /**
     * @throws ValidationException
     */
    public function getBranch(): Branch
    {
        $branch = $this->validated('Branch');

        $branch = Branch::query()->where('BranchID', $branch)->latest()->first();
        if ($branch instanceof Branch) {
            return $branch;
        }

        throw ValidationException::withMessages(['Branch' => 'Branch is not found.']);
    }
}
