<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordPolicy implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $min = (int) config('noorhan.auth.password_min_length', 8);

        if (strlen($value) < $min) {
            $fail("The :attribute must be at least {$min} characters.");
            return;
        }
        if (! preg_match('/[a-z]/', $value) || ! preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain both uppercase and lowercase letters.');
            return;
        }
        if (! preg_match('/\d/', $value)) {
            $fail('The :attribute must contain at least one number.');
            return;
        }
        if (! preg_match('/[^a-zA-Z0-9]/', $value)) {
            $fail('The :attribute must contain at least one symbol.');
        }
    }
}