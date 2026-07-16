<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label(__('question'))
                    ->limit(50)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('answer')
                    ->label(__('answer'))
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order')
                    ->label(__('faq_order'))
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label(__('status'))
                    ->boolean()
                    ->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('changeOrder')
                    ->label(__('change_order'))
                    ->visible(fn () => auth('web')->user()->can('reorder_faq'))
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->form(function (Faq $record) {
                        $orders = $record->newQuery()
                            ->orderBy('order')
                            ->pluck('order', 'order')
                            ->toArray();

                        return [
                            Select::make('order')
                                ->label(__('select_new_order'))
                                ->options($orders)
                                ->default($record->order)
                                ->rule('required')
                                ->searchable(),
                        ];
                    })
                    ->action(function (array $data, Faq $record) {
                        $newOrder = $data['order'];
                        if ($newOrder == $record->order) {
                            return;
                        }
                        $existing = $record->newQuery()
                            ->where('order', $newOrder)
                            ->first();

                        if ($existing) {
                            $existing->update(['order' => $record->order]);
                        }
                        $record->update(['order' => $newOrder]);
                        Notification::make()
                            ->title(__('order_updated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('change_faq_order'))
                    ->modalDescription(__('change_faq_order_description')),
                EditAction::make(),
                DeleteAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->visible(fn () => auth('web')->user()->can('activate_any_faq'))
                        ->label(__('activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('deactivate')
                        ->visible(fn () => auth('web')->user()->can('activate_any_faq'))
                        ->label(__('deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }
}
