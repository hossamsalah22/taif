<?php

namespace App\Filament\Resources\Children\Tables;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Filament\Resources\Children\ChildResource;
use App\Models\Child;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChildrenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label(__('Child ID'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label(__('Child Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('parent.name')
                    ->label(__('Parent'))
                    ->description(fn (Child $record): ?string => $record->parent?->phone ? ltrim($record->parent->phone, '+') : null)
                    ->searchable(),
                TextColumn::make('age')
                    ->label(__('Age'))
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->formatStateUsing(fn ($state) => GenderEnum::label($state))
                    ->color(fn ($state) => GenderEnum::color($state))
                    ->badge(),
                TextColumn::make('autism_level')
                    ->label(__('Autism Level'))
                    ->formatStateUsing(fn (AutismLevelEnum $state) => AutismLevelEnum::label($state))
                    ->color(fn (AutismLevelEnum $state) => AutismLevelEnum::color($state))
                    ->badge(),
                TextColumn::make('child_learning_plans_count')
                    ->label(__('Learning Plans'))
                    ->counts('childLearningPlans')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('autism_level')
                    ->label(__('Autism Level'))
                    ->options(AutismLevelEnum::options()),
                SelectFilter::make('gender')
                    ->label(__('Gender'))
                    ->options(GenderEnum::options()),
                TernaryFilter::make('has_learning_plan')
                    ->label(__('Learning Plan'))
                    ->trueLabel(__('Has a learning plan'))
                    ->falseLabel(__('No learning plan yet'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('childLearningPlans'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('childLearningPlans'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    Action::make('viewProgressDetails')
                        ->label(__('View Progress Details'))
                        ->icon('heroicon-o-chart-bar')
                        ->color('success')
                        ->url(fn (Child $record): string => ChildResource::getUrl('progress', ['record' => $record]))
                        ->visible(fn (Child $record) => auth()->user()->can('view_progress_details_child')),
                    Action::make('force_retest')
                        ->label(__('Force Re-test'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('Override Assessment Lock'))
                        ->modalDescription(__('Are you sure you want to allow this child to take the assessment again? This will bypass the maximum attempts limit.'))
                        ->action(fn (Child $record) => $record->update(['force_re_test' => true]))
                        ->visible(fn (Child $record) => ! $record->force_re_test),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
