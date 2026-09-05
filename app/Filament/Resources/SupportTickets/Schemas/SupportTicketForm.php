<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Enums\SupportTicketStatusEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class SupportTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Parent Metadata Panel'))
                    ->schema([
                        Placeholder::make('user.name')
                            ->label(__('Parent Name'))
                            ->content(fn ($record) => $record?->user?->name),
                        Placeholder::make('user.phone')
                            ->label(__('Phone Number'))
                            ->content(fn ($record) => $record?->user?->phone),
                    ])->columns(2),

                Section::make(__('Problem Summary & Body'))
                    ->schema([
                        Placeholder::make('reference_number')
                            ->label(__('Ticket Reference Header'))
                            ->content(fn ($record) => $record?->reference_number),
                        Placeholder::make('title')
                            ->label(__('Title'))
                            ->content(fn ($record) => $record?->title),
                        Placeholder::make('description')
                            ->label(__('Detailed Description'))
                            ->content(fn ($record) => $record?->description)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('Agent Action Panel'))
                    ->schema([
                        Select::make('assigned_admin_id')
                            ->label(__('Assigned Specialist'))
                            ->relationship('assignedAdmin', 'name')
                            ->searchable(),
                        Select::make('status')
                            ->label(__('Ticket Status'))
                            ->disabled(fn ($record) => $record->status === SupportTicketStatusEnum::CLOSED)
                            ->options(SupportTicketStatusEnum::options())
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
