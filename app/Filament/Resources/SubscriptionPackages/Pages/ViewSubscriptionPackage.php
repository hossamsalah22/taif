<?php

namespace App\Filament\Resources\SubscriptionPackages\Pages;

use App\Filament\Resources\SubscriptionPackages\SubscriptionPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscriptionPackage extends ViewRecord
{
    protected static string $resource = SubscriptionPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
