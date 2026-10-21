<?php

namespace App\Filament\Resources\ClinicalProgressReports\Tables;

use App\Models\LearningPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClinicalProgressReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('child.name')
                    ->label(__('Child'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('plan.name')
                    ->label(__('Learning Plan'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label(__('Report Title'))
                    ->searchable(),
                IconColumn::make('is_visible_to_parent')
                    ->label(__('Visible to Parent'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('child_id')
                    ->label(__('Child'))
                    ->relationship('child', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('learning_plan_id')
                    ->label(__('Learning Plan'))
                    ->relationship('plan', 'name')
                    ->getOptionLabelFromRecordUsing(fn (LearningPlan $record): string => $record->name)
                    ->preload(),
                TernaryFilter::make('is_visible_to_parent')
                    ->label(__('Visible to Parent'))
                    ->trueLabel(__('Shared with the parent'))
                    ->falseLabel(__('Hidden from the parent')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
