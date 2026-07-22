<?php

namespace App\Filament\Resources\SubscriptionPackages;

use App\Filament\Resources\MainResource;
use App\Filament\Resources\SubscriptionPackages\Pages\CreateSubscriptionPackage;
use App\Filament\Resources\SubscriptionPackages\Pages\EditSubscriptionPackage;
use App\Filament\Resources\SubscriptionPackages\Pages\ListSubscriptionPackages;
use App\Filament\Resources\SubscriptionPackages\Pages\ViewSubscriptionPackage;
use App\Filament\Resources\SubscriptionPackages\Schemas\SubscriptionPackageForm;
use App\Filament\Resources\SubscriptionPackages\Schemas\SubscriptionPackageInfolist;
use App\Filament\Resources\SubscriptionPackages\Tables\SubscriptionPackagesTable;
use App\Models\SubscriptionPackage;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubscriptionPackageResource extends MainResource
{
    protected static ?string $model = SubscriptionPackage::class;

    public static function getNavigationGroup(): ?string
    {
        return __('Subscriptions Management');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SubscriptionPackageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubscriptionPackageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionPackages::route('/'),
            'create' => CreateSubscriptionPackage::route('/create'),
            'view' => ViewSubscriptionPackage::route('/{record}'),
            'edit' => EditSubscriptionPackage::route('/{record}/edit'),
        ];
    }
}
