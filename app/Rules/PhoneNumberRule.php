<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberRule implements Rule
{
    protected string $countryCode;

    public function __construct(string $countryCode = 'SA')
    {
        $this->countryCode = $countryCode;
    }

    public function passes($attribute, $value): bool
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        try {
            // Parse the number without specifying the region to allow all countries
            $phoneNumber = $phoneNumberUtil->parse($value, $this->countryCode);

            return $phoneNumberUtil->isValidNumberForRegion($phoneNumber, $this->countryCode);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    public function message(): string
    {
        return __('validation.phone_number');
    }
}
