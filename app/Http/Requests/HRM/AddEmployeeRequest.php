<?php

namespace App\Http\Requests\HRM;

use App\Enums\Employee\GenderEnum;
use App\Exceptions\ErroredException;
use App\Models\CrmBranch;
use App\Models\Department;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AddEmployeeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'FirstName' => ['required', 'string', 'max:250'],
            'MiddleName' => ['nullable', 'string', 'max:250'],
            'LastName' => ['required', 'string', 'max:250'],
            'Phone' => ['required', 'string', /*'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'*/],
            'Email' => ['required', 'email:rfc,dns', 'max:250'],
            'Department' => ['required'],
            'Branch' => ['required'],
            'Gender' => ['required', 'string'],
            'JobTitle' => ['required', 'string', 'max:250'],
            'JoinDate' => ['required'],
            'DateOfBirth' => ['required'],
            'Address' => ['nullable', 'string'],
            'image' => ['nullable', Rule::imageFile()->max('2mb')],
            'CreateUser' => ['sometimes', 'accepted']
        ];
    }

    public function addUser(): bool
    {
        return $this->has('CreateUser') && $this->validated('CreateUser') === 'on';
    }

    public function getImage(): ?UploadedFile
    {
        if ($this->hasFile('image')) {
            return $this->file('image');
        }
        return null;
    }

    public function getJoinDate(): Carbon
    {
        $date = $this->_date($this->validated('JoinDate'), 'JoinDate');

        if ($date->greaterThan(Carbon::now())) {
            throw ValidationException::withMessages(['JoinDate' => 'invalid date should be today or in the past']);
        }

        return $date;
    }

    protected function _date(string $value, string $name): Carbon
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $value)?->startOfDay();
        } catch (InvalidFormatException) {
            throw ValidationException::withMessages([$name => 'invalid date format eg 2020-03-29']);
        }

        if (!$date instanceof Carbon) {
            throw ValidationException::withMessages([$name => 'invalid date format eg 2020-03-29']);
        }
        return $date;
    }

    public function getDateOfBirth(): Carbon
    {
        $date = $this->_date($this->validated('DateOfBirth'), 'DateOfBirth');

        if ($date->greaterThan(Carbon::now()->subYears(18))) {
            throw ValidationException::withMessages(['DateOfBirth' => 'employee should be 18 years or older']);
        }

        return $date;
    }

    public function getBranch(): CrmBranch
    {
        $branch = CrmBranch::query()->where('BranchID', $this->validated('Branch'))->first();
        if ($branch instanceof CrmBranch) {
            return $branch;
        }
        throw ValidationException::withMessages(['Branch' => 'invalid branch']);
    }

    public function getGender(): GenderEnum
    {
        try {
            return GenderEnum::fromValue($this->validated('Gender'));
        } catch (ErroredException) {
        }
        throw ValidationException::withMessages(['Gender' => 'invalid gender']);
    }

    public function getDepartment(): Department
    {
        $department = Department::query()->where('DepartmentID', $this->validated('Department'))->first();
        if ($department instanceof Department) {
            return $department;
        }
        throw ValidationException::withMessages(['Department' => 'invalid department']);
    }
}
