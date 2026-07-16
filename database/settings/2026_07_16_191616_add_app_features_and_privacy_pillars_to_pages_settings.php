<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('pages.about_app_header_en', '');
        $this->migrator->add('pages.about_app_header_ar', '');
        $this->migrator->add('pages.about_app_sub_header_en', '');
        $this->migrator->add('pages.about_app_sub_header_ar', '');
        $this->migrator->add('pages.app_features', []);
        $this->migrator->add('pages.help_center_slogan_en', '');
        $this->migrator->add('pages.help_center_slogan_ar', '');
        $this->migrator->add('pages.privacy_pillars', []);
    }
};
