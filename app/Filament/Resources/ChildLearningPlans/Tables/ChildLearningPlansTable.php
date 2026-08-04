<?php

namespace App\Filament\Resources\ChildLearningPlans\Tables;

use App\Enums\AutismLevelEnum;
use App\Enums\ChildLearningPlanStatusEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class ChildLearningPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('child.name')
                    ->label(__('Child Name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('learningPlan.name')
                    ->label(__('Assigned Plan Title'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('child.autism_level')
                    ->label(__('Severity Alignment'))
                    ->formatStateUsing(fn (AutismLevelEnum $state) => AutismLevelEnum::label($state))
                    ->color(fn (AutismLevelEnum $state) => AutismLevelEnum::color($state))
                    ->badge(),
                ViewColumn::make('progress')
                    ->label(__('Execution Progress'))
                    ->view('filament.tables.columns.progress-bar'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => ChildLearningPlanStatusEnum::label($state))
                    ->color(fn ($state) => ChildLearningPlanStatusEnum::color($state))
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('view_progress')
                    ->label(__('View Progress Details'))
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn ($record) => route('filament.admin.resources.child-learning-plans.progress', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
