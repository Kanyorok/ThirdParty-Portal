<?php

namespace App\Http\Requests\HRM;

use App\Enums\Employee\GenderEnum;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeePersonalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow authorized users to update their personal details
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'FirstName' => ['required', 'string', 'max:255'],
            'MiddleName' => ['nullable', 'string', 'max:255'],
            'LastName' => ['required', 'string', 'max:255'],
            'Phone' => ['required', 'string', 'max:20'],
            'Email' => ['required', 'email:rfc,dns', 'max:250'],
            'DateOfBirth' => ['required'],
            'Gender' => ['required', Rule::enum(GenderEnum::class)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'FirstName.required' => 'First name is required',
            'LastName.required' => 'Last name is required',
            'Phone.required' => 'Phone number is required',
            'Email.required' => 'Email address is required',
            'Email.email' => 'Please enter a valid email address',
            'Gender.required' => 'Please select a gender',
            'Gender.enum' => 'Invalid gender selection',
        ];
    }

    public function getDateOfBirth(): Carbon
    {
        $date = AddEmployeeRequest::formatedDate($this->validated('DateOfBirth'), 'DateOfBirth');
        if ($date->greaterThan(Carbon::now()->subYears(18))) {
            throw ValidationException::withMessages(['DateOfBirth' => 'employee should be 18 years or older']);
        }

        return $date;
    }
}
