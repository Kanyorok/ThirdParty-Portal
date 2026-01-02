<?php

namespace App\Rules\ThirdParty;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRegistrationNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Validate company registration number format
        // Adjust pattern based on your country's requirements
        $minLength = 3;
        $maxLength = 50;

        if (strlen($value) < $minLength || strlen($value) > $maxLength) {
            $fail("The registration number must be between {$minLength} and {$maxLength} characters.");
        }
    }
}
