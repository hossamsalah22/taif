<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('is_active')
                    ->label(__('Account Status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Active') : __('Suspended'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
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
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    Action::make('suspend')
                        ->label(__('Suspend Account'))
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (User $record): bool => $record->is_active)
                        ->requiresConfirmation()
                        ->modalHeading(__('Suspend this account?'))
                        ->modalDescription(__('The parent will be signed out of the app on every device and cannot sign in until the account is activated again.'))
                        ->modalSubmitActionLabel(__('Suspend'))
                        ->action(function (User $record): void {
                            $record->update(['is_active' => false]);
                            $record->tokens()->delete();

                            Notification::make()
                                ->title(__('Account Suspended'))
                                ->body(__('The user account has been suspended and their sessions have been revoked.'))
                                ->warning()
                                ->send();
                        }),
                    Action::make('activate')
                        ->label(__('Activate Account'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (User $record): bool => ! $record->is_active)
                        ->requiresConfirmation()
                        ->modalHeading(__('Activate this account?'))
                        ->modalDescription(__('The parent will be able to sign in to the app again.'))
                        ->modalSubmitActionLabel(__('Activate'))
                        ->action(function (User $record): void {
                            $record->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('Account Activated'))
                                ->success()
                                ->send();
                        }),
                    Action::make('free_subscription')
                        ->visible(fn ($record) => $record->children()->exists() && ! $record->subscriptions()->where('is_free', true)->exists())
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
                            $record->subscriptions()->create([
                                'status' => 'active',
                                'start_date' => now(),
                                'expiry_date' => now()->addDays($data['duration_days']),
                                'is_free' => true,
                                'amount_paid' => 0,
                            ]);

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
                ]),
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
