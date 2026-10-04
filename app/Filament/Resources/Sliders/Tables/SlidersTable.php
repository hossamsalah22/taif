<?php

namespace App\Filament\Resources\Sliders\Tables;

use App\Models\Slider;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                SpatieMediaLibraryImageColumn::make('slider_ar_large')
                    ->collection('slider_ar_large')
                    ->label(__('Image')),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('Sort Order'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('changeOrder')
                    ->label(__('change_order'))
                    ->visible(fn () => auth('web')->user()->can('Reorder:Slider'))
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->form(function (Slider $record) {
                        $orders = $record->newQuery()
                            ->orderBy('sort_order')
                            ->pluck('sort_order', 'sort_order')
                            ->toArray();

                        return [
                            Select::make('sort_order')
                                ->label(__('select_new_order'))
                                ->options($orders)
                                ->default($record->sort_order)
                                ->rule('required')
                                ->searchable(),
                        ];
                    })
                    ->action(function (array $data, Slider $record) {
                        $newOrder = $data['sort_order'];
                        if ($newOrder == $record->sort_order) {
                            return;
                        }
                        $existing = $record->newQuery()
                            ->where('sort_order', $newOrder)
                            ->first();

                        if ($existing) {
                            $existing->update(['sort_order' => $record->sort_order]);
                        }
                        $record->update(['sort_order' => $newOrder]);
                        Notification::make()
                            ->title(__('order_updated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('change_silder_order'))
                    ->modalDescription(__('change_silder_order_description')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->visible(fn () => auth('web')->user()->can('ActivateAny:Slider'))
                        ->label(__('activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('deactivate')
                        ->visible(fn () => auth('web')->user()->can('ActivateAny:Slider'))
                        ->label(__('deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
