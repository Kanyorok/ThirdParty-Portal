<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class isDomain implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string = null): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->_isDomain($value)) {
            $fail('The :attribute must a fully qualified domain.');
        }
    }

    protected function _isDomain(string $domain_name): bool
    {
        //FILTER_VALIDATE_URL checks length but..why not? so we dont move forward with more expensive operations
        $domain_len = strlen($domain_name);
        if ($domain_len < 3 || $domain_len > 253) {
            return false;
        }

        //getting rid of HTTP/S just in case was passed.
        if (stripos($domain_name, 'http://') === 0) {
            $domain_name = substr($domain_name, 7);
        } elseif (stripos($domain_name, 'https://') === 0) {
            $domain_name = substr($domain_name, 8);
        }

        //we dont need the www either
        if (stripos($domain_name, 'www.') === 0) {
            $domain_name = substr($domain_name, 4);
        }

        //Checking for a '.' at least, not in the beginning nor end, since http://.abcd. is reported valid
        if (!str_contains($domain_name, '.') || $domain_name[strlen($domain_name) - 1] === '.' || $domain_name[0] == '.') {
            return false;
        }

        //now we use the FILTER_VALIDATE_URL, concatenating http so we can use it, and return BOOL
        return !((filter_var('http://' . $domain_name, FILTER_VALIDATE_URL) === false));
    }
}
