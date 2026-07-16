<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PagesSettings extends Settings
{
    public string $about_app_header_en;

    public string $about_app_header_ar;

    public string $about_app_sub_header_en;

    public string $about_app_sub_header_ar;

    public array $app_features;

    public string $help_center_slogan_en;

    public string $help_center_slogan_ar;

    public array $privacy_pillars;

    public string $about_us_en;

    public string $about_us_ar;

    public string $privacy_policy_en;

    public string $privacy_policy_ar;

    public string $terms_and_conditions_en;

    public string $terms_and_conditions_ar;

    public static function group(): string
    {
        return 'pages';
    }
}
