<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.phone', null);
        $this->migrator->add('general.whatsapp', null);
        $this->migrator->add('general.email', null);
        $this->migrator->add('general.app_store_link', null);
        $this->migrator->add('general.play_store_link', null);
        $this->migrator->add('general.tax', 15);
        $this->migrator->add('general.tax_type', 'percentage');
        $this->migrator->add('general.commission', 15);
        $this->migrator->add('general.commission_type', 'percentage');
        $this->migrator->add('general.facebook', '');
        $this->migrator->add('general.instagram', '');
        $this->migrator->add('general.snapchat', '');
        $this->migrator->add('general.linkedin', '');
        $this->migrator->add('general.tiktok', '');
    }
};
