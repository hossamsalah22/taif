<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Enums\SupportTicketStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')
                    ->label(__('Ticket Number'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('Parent'))
                    ->searchable(),
                TextColumn::make('title')
                    ->label(__('Subject'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (TextColumn $column): ?string => strlen((string) $column->getState()) > 50 ? $column->getState() : null),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('assignedAdmin.name')
                    ->label(__('Assigned Specialist'))
                    ->placeholder(__('Unassigned'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Date Submitted'))
                    ->dateTime()
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(SupportTicketStatusEnum::options())
                    ->multiple(),
                SelectFilter::make('assigned_admin_id')
                    ->label(__('Assigned Specialist'))
                    ->relationship('assignedAdmin', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('unassigned')
                    ->label(__('Unassigned only'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('assigned_admin_id')),
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
