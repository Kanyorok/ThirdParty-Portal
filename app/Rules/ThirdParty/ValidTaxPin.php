<?php

namespace App\Rules\ThirdParty;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTaxPin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Validate tax PIN format (e.g., KRA PIN: A000000000X)
        // Adjust pattern based on your country's requirements
        if (!preg_match('/^[A-Z]\d{9}[A-Z]$/i', $value)) {
            $fail('The tax PIN format is invalid. Expected format: A000000000X');
        }
    }
}
