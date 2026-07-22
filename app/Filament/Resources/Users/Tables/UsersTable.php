<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email Address'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Mobile Number'))
                    ->formatStateUsing(fn (string $state): string => ltrim($state, '+'))
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('children_count')
                    ->label(__('Children Count'))
                    ->counts('children')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('Account Status'))
                    ->afterStateUpdated(function (User $record, $state) {
                        if (! $state) {
                            $record->tokens()->delete();
                            Notification::make()
                                ->title(__('Account Suspended'))
                                ->body(__('The user account has been suspended and their sessions have been revoked.'))
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('Account Activated'))
                                ->success()
                                ->send();
                        }
                    }),
                TextColumn::make('created_at')
                    ->label(__('Registration Date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                TernaryFilter::make('is_active')
                    ->label(__('Account Status'))
                    ->boolean()
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Suspended')),
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
                Action::make('free_subscription')
                    ->label(__('Free Subscription'))
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->form([
                        TextInput::make('duration_days')
                            ->label(__('Free Subscription Duration (Days)'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (User $record, array $data) {
                        // Logic for creating a free subscription for the user
                        // This will be added when subscriptions logic is complete
                        Notification::make()
                            ->title(__('Free Subscription Granted'))
                            ->body(__("User granted free access for {$data['duration_days']} days."))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->before(function (User $record, DeleteAction $action) {
                        $hasActiveSubscriptions = $record->subscriptions()->where('status', 'active')->exists();
                        if ($hasActiveSubscriptions) {
                            $action->requiresConfirmation()
                                ->modalHeading(__('Active Subscriptions Detected'))
                                ->modalDescription(__('I acknowledge that this account has active subscriptions and that deleting it will freeze/terminate ongoing platform access for these packages.'))
                                ->modalSubmitActionLabel(__('Acknowledge & Delete'));
                        }
                    })
                    ->after(function (User $record) {
                        $record->tokens()->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
