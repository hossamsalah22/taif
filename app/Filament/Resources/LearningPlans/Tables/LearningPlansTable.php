<?php

namespace App\Filament\Resources\LearningPlans\Tables;

use App\Enums\AutismLevelEnum;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Models\LearningPlan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LearningPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('name'))->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($query) use ($search) {
                            $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ['%'.strtolower($search).'%'])
                                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) LIKE ?', ['%'.strtolower($search).'%']);
                        });
                    }),
                TextColumn::make('autism_level')
                    ->label(__('Severity Level'))
                    ->formatStateUsing(fn (AutismLevelEnum $state) => AutismLevelEnum::label($state))
                    ->color(fn (AutismLevelEnum $state) => AutismLevelEnum::color($state))
                    ->badge(),
                TextColumn::make('goals_count')
                    ->counts('goals')
                    ->label(__('Goals'))
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'danger')
                    ->tooltip(fn (int $state): ?string => $state > 0 ? null : __('This plan has no goals yet, so a child assigned to it has nothing to learn.'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('weekly_sessions_count')
                    ->label(__('Weekly Sessions Count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('phase_duration')
                    ->label(__('Phase Duration'))
                    ->placeholder('-'),
                TextColumn::make('max_daily_goals')
                    ->label(__('Max Daily Goals'))
                    ->numeric()
                    ->placeholder(__('No limit'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_daily_lessons')
                    ->label(__('Max Daily Lessons'))
                    ->numeric()
                    ->placeholder(__('No limit'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_daily_exercises')
                    ->label(__('Max Daily Exercises'))
                    ->numeric()
                    ->placeholder(__('No limit'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('child_learning_plans_count')
                    ->counts('childLearningPlans')
                    ->label(__('Assigned Children Count'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('autism_level')
                    ->label(__('Severity Level'))
                    ->options(AutismLevelEnum::options()),
            ])
            ->emptyStateHeading(__('No learning plans yet'))
            ->emptyStateDescription(__('Create a plan, add goals to it, then assign it to children with the same severity level.'))
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('assign_to_children')
                    ->label(__('Assign to Children'))
                    ->icon('heroicon-o-user-group')
                    ->form([
                        Select::make('child_ids')
                            ->label(__('Select Children'))
                            ->multiple()
                            ->searchable()
                            ->options(function (LearningPlan $record) {
                                return Child::where('autism_level', $record->autism_level)
                                    ->whereDoesntHave('childLearningPlans', function ($query) {
                                        $query->where('status', 'in_progress');
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                    ])
                    ->action(function (array $data, LearningPlan $record): void {
                        foreach ($data['child_ids'] as $childId) {
                            ChildLearningPlan::create([
                                'child_id' => $childId,
                                'learning_plan_id' => $record->id,
                                'status' => 'in_progress',
                            ]);
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('Assign Plan to Children'))
                    ->modalDescription(__('This will assign the plan to the selected children. Only children with matching severity level and no active plans are shown.'))
                    ->disabled(fn (LearningPlan $record): bool => $record->goals_count === 0)
                    ->tooltip(fn (LearningPlan $record): ?string => $record->goals_count === 0 ? __('Add goals to the plan before assigning it.') : null)
                    ->after(function () {
                        Notification::make()
                            ->title(__('Plan assigned successfully'))
                            ->success()
                            ->send();
                    }),
                ReplicateAction::make()
                    ->excludeAttributes(['child_learning_plans_count', 'goals_count']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
