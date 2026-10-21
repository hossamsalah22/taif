<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Enums\SupportTicketStatusEnum;
use App\Models\SupportTicket;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
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
                Action::make('assignToMe')
                    ->label(__('Assign to me'))
                    ->icon('heroicon-o-hand-raised')
                    ->color('primary')
                    ->visible(fn (SupportTicket $record): bool => $record->status !== SupportTicketStatusEnum::CLOSED
                        && $record->assigned_admin_id !== auth()->id())
                    ->action(function (SupportTicket $record): void {
                        $record->update([
                            'assigned_admin_id' => auth()->id(),
                            // Picking up a new ticket means someone is now working on it.
                            'status' => $record->status === SupportTicketStatusEnum::OPEN ? SupportTicketStatusEnum::IN_PROGRESS : $record->status,
                        ]);

                        Notification::make()
                            ->title(__('Ticket :number is now assigned to you', ['number' => $record->reference_number]))
                            ->success()
                            ->send();
                    }),
                ActionGroup::make([
                    Action::make('changeStatus')
                        ->label(__('Change status'))
                        ->icon('heroicon-o-arrow-path')
                        ->visible(fn (SupportTicket $record): bool => $record->status !== SupportTicketStatusEnum::CLOSED)
                        ->modalWidth('md')
                        ->fillForm(fn (SupportTicket $record): array => ['status' => $record->status->value])
                        ->schema([
                            Select::make('status')
                                ->label(__('Status'))
                                ->options(SupportTicketStatusEnum::options())
                                ->helperText(__('Closing a ticket stops further replies, and it cannot be reopened.'))
                                ->required(),
                        ])
                        ->action(function (SupportTicket $record, array $data): void {
                            $record->update(['status' => $data['status']]);

                            Notification::make()
                                ->title(__('Ticket status updated successfully.'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
