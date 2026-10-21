<?php

namespace App\Filament\Resources\ChildLearningPlans\Tables;

use App\Enums\AutismLevelEnum;
use App\Enums\ChildLearningPlanStatusEnum;
use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use App\Models\LearningPlan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->label(__('Assigned At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(ChildLearningPlanStatusEnum::options()->all()),
                SelectFilter::make('learning_plan_id')
                    ->label(__('Learning Plan'))
                    ->relationship('learningPlan', 'name')
                    ->getOptionLabelFromRecordUsing(fn (LearningPlan $record): string => $record->name)
                    ->preload(),
                SelectFilter::make('autism_level')
                    ->label(__('Autism Level'))
                    ->options(AutismLevelEnum::options())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $query, string $level): Builder => $query->whereRelation('child', 'autism_level', $level),
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('view_progress')
                    ->label(__('Progress Details'))
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn ($record) => ChildLearningPlanResource::getUrl('progress', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
