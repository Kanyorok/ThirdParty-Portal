<?php

namespace App\Http\Requests\Lead;

use App\Models\BR\Branch;
use App\Models\BR\SystemCodeDetail;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OnBoardingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'MemberClass' => [
                                   'required',
                                   'string',
                                  ],
                'Branch' => [
                                   'required',
                                   'string',
                                  ],
                'GovernmentID' => [
                                   'required',
                                   'string',
                                  ],
                'TaxNo' => [
                                   'required',
                                   'string',
                                  ],
                'DoB' => [
                                   'required',
                                   'string',
                                   'date',
                                   'date_format:Y-m-d',
                                  ],
                'County' => [
                                   'required',
                                   'string',
                                  ],
                'Address1' => [
                                   'required',
                                   'string',
                                  ],
                'Address2' => [
                                   'required',
                                   'string',
                                  ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getCountyCode(): string
    {
        /*if (DB::connection('brcbs')->table('t_Country')->where('CountryID', $this->validated('County'))->exists()) {
            return $this->validated('County');
        }*/
        throw ValidationException::withMessages(['County' => 'county code has to be ISO 3166-1 alpha-2']);
    }

    /**
     * @throws ValidationException
     */
    public function getMemberClass(): string
    {
        if (SystemCodeDetail::query()->where('ID', 'MemberClassID')->where('SubCodeID', $this->validated('MemberClass'))->exists()) {
            return $this->validated('MemberClass');
        }

        throw ValidationException::withMessages(['MemberClassID' => 'Member Class may be invalid.']);
    }

    /**
     * @throws ValidationException
     */
    public function getBranch(): Branch
    {
        $branch = Branch::query()->where('OurBranchID', $this->validated('Branch'))->first();
        if ($branch instanceof Branch) {
            return $branch;
        }

        throw ValidationException::withMessages(['Branch' => 'Branch may be invalid']);
    }

    /**
     * @throws ValidationException
     */
    public function getDob(): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $this->validated('DoB'));
        } catch (\Exception) {
        }

        throw ValidationException::withMessages(['DOB' => 'Invalid Date format eg 2000-01-31']);
    }
}
