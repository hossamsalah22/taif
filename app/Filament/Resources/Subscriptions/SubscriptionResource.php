<?php

namespace App\Filament\Resources\Subscriptions;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\MainResource;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\SubscriptionInvoice;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionInfolist;
use App\Filament\Resources\Subscriptions\Tables\SubscriptionsTable;
use App\Models\Subscription;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Read-only list of what parents subscribed to. Subscriptions are created by the app's checkout
 * or the "Free Subscription" action on a parent, never by hand here.
 */
class SubscriptionResource extends MainResource
{
    protected static ?string $model = Subscription::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::SUBSCRIPTIONS;

    protected static ?int $navigationSort = 0;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return SubscriptionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubscriptionInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
            'invoice' => SubscriptionInvoice::route('/{record}/invoice'),
        ];
    }
}
