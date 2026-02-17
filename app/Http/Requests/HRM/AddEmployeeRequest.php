<?php

namespace App\Http\Requests\HRM;

use App\Enums\Employee\GenderEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\HR\Employee;
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
            // E.164 format: +<country code><national number>, total length 8-15 digits
            'Phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'Email' => ['required', Rule::email()
                ->rfcCompliant(strict: false)
                ->validateMxRecord()
                ->preventSpoofing(), 'max:250'],
            'Department' => ['required'],
            'Branch' => ['required'],
            'Gender' => ['required', 'string'],
            'JobTitle' => ['required', 'string', 'max:250'],
            'JoinDate' => ['required'],
            'DateOfBirth' => ['required'],
            'Address' => ['nullable', 'string', 'max:220'],
            'image' => ['nullable', Rule::imageFile()->max('2mb')],
            'CreateUser' => ['sometimes', 'accepted'],
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
        $date = self::formatedDate($this->validated('JoinDate'), 'JoinDate');

        if ($date->greaterThan(Carbon::now())) {
            throw ValidationException::withMessages(['JoinDate' => 'invalid date should be today or in the past']);
        }

        return $date;
    }

    public static function formatedDate(string $value, string $name): Carbon
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $value)?->startOfDay();
        } catch (InvalidFormatException) {
            throw ValidationException::withMessages([$name => 'invalid date format eg 2020-03-29']);
        }

        if (! $date instanceof Carbon) {
            throw ValidationException::withMessages([$name => 'invalid date format eg 2020-03-29']);
        }

        return $date;
    }

    public function getDateOfBirth(): Carbon
    {
        $date = self::formatedDate($this->validated('DateOfBirth'), 'DateOfBirth');

        if ($date->greaterThan(Carbon::now()->subYears(18))) {
            throw ValidationException::withMessages(['DateOfBirth' => 'employee should be 18 years or older']);
        }

        return $date;
    }

    public function getBranch(): Branch
    {
        $branch = Branch::query()->where('BranchID', $this->validated('Branch'))->first();
        if ($branch instanceof Branch) {
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

    public function getPhoneNumber(): string
    {
        $input = (string) $this->validated('Phone');
        // Trim spaces and enforce E.164 normalization
        $normalized = preg_replace('/\s+/', '', $input);

        if (! is_string($normalized) || ! preg_match('/^\+[1-9]\d{7,14}$/', $normalized)) {
            throw ValidationException::withMessages(['Phone' => 'invalid phone number, use E.164 e.g. +12025550123']);
        }

        $userDup = User::where('Phone', $normalized)->exists();
        $empDup = Employee::where('Phone', $normalized)->exists();

        if ($userDup || $empDup) {
            throw ValidationException::withMessages(['Phone' => 'phone already exists']);
        }

        return $normalized;
    }

    public function getEmail(): string
    {
        $email = $this->validated('Email');
        if (User::where('Email', $email)->exists() || Employee::where('Email', $email)->exists()) {
            throw ValidationException::withMessages(['Email' => 'email already exists']);
        }

        return $email;
    }
}
