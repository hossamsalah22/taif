<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

function formatDate($date): ?string
{
    return $date ? Carbon::parse($date)->format('Y-m-d H:i:s') : null;
}

if (! function_exists('___')) {
    function ___($key, $replace = [], $locale = null)
    {
        // Check if the key exists
        if (Lang::has($key, $locale)) {
            return trans($key, $replace, $locale);
        }

        // Construct the fallback key
        $fallbackKey = "translate.$key";

        // Check if the fallback key exists
        if (Lang::has($fallbackKey, $locale)) {
            return trans($fallbackKey, $replace, $locale);
        }

        // Return the original key if no translation is found
        return $key;
    }
}

function prepare_phone(string $phone, ?string $country = 'SA'): string
{
    try {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($phone, $country);

        return $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);
    } catch (NumberParseException $e) {
        return $e->getMessage();
    }
}
