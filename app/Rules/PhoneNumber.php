<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts common human formats (+7 (999) 123-45-67, 8 999 123 45 67, +77001234567)
 * and validates the amount of actual digits instead of the raw string length.
 */
class PhoneNumber implements ValidationRule
{
    private const MIN_DIGITS = 10;

    private const MAX_DIGITS = 15;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Некорректный формат телефона.');

            return;
        }

        if (preg_match('/^\+?[0-9\s\-().]+$/u', $value) !== 1) {
            $fail('Телефон может содержать только цифры и символы + - ( ) . и пробелы.');

            return;
        }

        $digits = self::digits($value);
        $length = strlen($digits);

        if ($length < self::MIN_DIGITS) {
            $fail('Телефон должен содержать минимум '.self::MIN_DIGITS.' цифр.');

            return;
        }

        if ($length > self::MAX_DIGITS) {
            $fail('Телефон должен содержать не более '.self::MAX_DIGITS.' цифр.');
        }
    }

    public static function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * Normalizes a valid phone into E.164-like form: +<digits>.
     */
    public static function normalize(string $value): string
    {
        $digits = self::digits($value);

        if ($digits === '') {
            return $value;
        }

        // Local Russian/Kazakh style 8XXXXXXXXXX → +7XXXXXXXXXX
        if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
            $digits = '7'.substr($digits, 1);
        }

        return '+'.$digits;
    }
}
