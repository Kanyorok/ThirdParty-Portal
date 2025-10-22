<?php

namespace App\Http\Requests\HRM;

use App\Enums\Employee\GenderEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\HRM\Employee;
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
            'Phone' => ['required', 'string', 'max:20', 'regex:/(^\+?254\d{9}$)|(^0\d{9}$)|(^\d{9}$)/'],
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

        if (!$date instanceof Carbon) {
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
        $input = (string)$this->validated('Phone');
        $digits = preg_replace('/\D+/', '', $input);

        // Normalize to E.164 (Kenya)
        if (str_starts_with($digits, '254') && strlen($digits) === 12) {
            $normalized = '+' . $digits; // 2547XXXXXXXX
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $normalized = '+254' . substr($digits, 1); // 07XXXXXXXX
        } elseif (strlen($digits) === 9 && str_starts_with($digits, '7')) {
            $normalized = '+254' . $digits; // 7XXXXXXXX
        } else {
            throw ValidationException::withMessages(['Phone' => 'invalid Kenyan phone number']);
        }

        // Compare by canonical forms to avoid format duplicates
        $suffix9 = substr(preg_replace('/\D+/', '', $normalized), -9); // 7XXXXXXXX
        $variants = ['+254' . $suffix9, '0' . $suffix9, $suffix9];

        $userDup = User::where(function ($q) use ($normalized, $variants, $suffix9) {
            $q->where('Phone', $normalized)
                ->orWhereIn('Phone', $variants)
                ->orWhere('Phone', 'like', '%' . $suffix9);
        })->exists();

        $empDup = Employee::where(function ($q) use ($normalized, $variants, $suffix9) {
            $q->where('Phone', $normalized)
                ->orWhereIn('Phone', $variants)
                ->orWhere('Phone', 'like', '%' . $suffix9);
        })->exists();

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
