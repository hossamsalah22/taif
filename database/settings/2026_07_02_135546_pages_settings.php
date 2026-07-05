<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $locales = array_keys(config('app.locales'));
        $translatedFields = ['about_us', 'privacy_policy', 'terms_and_conditions'];
        foreach ($locales as $locale) {
            foreach ($translatedFields as $field) {
                $this->migrator->add("pages.{$field}_{$locale}", ucfirst(str_replace('_', ' ', $field)));
            }
        }
    }
};
