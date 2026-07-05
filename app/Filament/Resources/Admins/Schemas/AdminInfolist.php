<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AdminInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin_profile_header'))
                    ->columnSpanFull()
                    ->components([
                        Tabs::make(__('admin_profile_tabs'))
                            ->tabs([
                                Tab::make(__('profile'))
                                    ->components([
                                        TextEntry::make('name')
                                            ->label(__('name')),
                                        TextEntry::make('email')
                                            ->label(__('email')),
                                        TextEntry::make('phone')
                                            ->label(__('phone')),
                                        TextEntry::make('roles.name')
                                            ->label(__('roles'))
                                            ->badge(),
                                        TextEntry::make('is_active')
                                            ->label(__('is_active'))
                                            ->formatStateUsing(fn ($state) => $state ? __('active') : __('suspended'))
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                                        TextEntry::make('created_at')
                                            ->label(__('created_at'))
                                            ->dateTime(),
                                    ])->columns(2),
                                Tab::make(__('permissions'))
                                    ->schema([
                                        ViewEntry::make('permissions')
                                            ->view('filament.infolists.admin-permissions-table'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
