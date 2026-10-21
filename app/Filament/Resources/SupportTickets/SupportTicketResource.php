<?php

namespace App\Filament\Resources\SupportTickets;

use App\Enums\NavigationGroupEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\MainResource;
use App\Filament\Resources\SupportTickets\Pages\CreateSupportTicket;
use App\Filament\Resources\SupportTickets\Pages\EditSupportTicket;
use App\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use App\Filament\Resources\SupportTickets\Schemas\SupportTicketForm;
use App\Filament\Resources\SupportTickets\Tables\SupportTicketsTable;
use App\Models\SupportTicket;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SupportTicketResource extends MainResource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::OPERATIONS;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Number of new tickets nobody has picked up yet; hidden when there are none.
     */
    public static function getNavigationBadge(): ?string
    {
        $openTickets = SupportTicket::where('status', SupportTicketStatusEnum::OPEN)->count();

        return $openTickets > 0 ? (string) $openTickets : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('Open tickets waiting for a reply');
    }

    public static function form(Schema $schema): Schema
    {
        return SupportTicketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportTicketsTable::configure($table);
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
            'index' => ListSupportTickets::route('/'),
            // 'create' => CreateSupportTicket::route('/create'),
            'edit' => EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
