<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Enums\SupportTicketStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')
                    ->label(__('Ticket Reference ID'))
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('Parent Account Name'))
                    ->searchable(),
                TextColumn::make('title')
                    ->label(__('Problem Title Summary'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('status')
                    ->label(__('Ticket Status Component'))
                    ->formatStateUsing(fn (SupportTicketStatusEnum $state) => $state->getLabel())
                    ->badge()
                    ->searchable(),
                TextColumn::make('assignedAdmin.name')
                    ->label(__('Assigned Specialist'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Date Submitted'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(SupportTicketStatusEnum::options()),
            ])
            ->recordActions([
                EditAction::make()->label(__('View & Manage')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
