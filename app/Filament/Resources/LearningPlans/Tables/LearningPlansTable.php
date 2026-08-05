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
                TextColumn::make('severity_level')
                    ->label(__('Severity Level'))
                    ->formatStateUsing(fn (AutismLevelEnum $state) => AutismLevelEnum::label($state))
                    ->color(fn (AutismLevelEnum $state) => AutismLevelEnum::color($state))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
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
                    ->searchable(),
                TextColumn::make('max_daily_goals')
                    ->label(__('Max Daily Goals'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_daily_lessons')
                    ->label(__('Max Daily Lessons'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_daily_exercises')
                    ->label(__('Max Daily Exercises'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('child_learning_plans_count')
                    ->counts('childLearningPlans')
                    ->label(__('Assigned Children Count'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
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
                                return Child::where('autism_level', $record->severity_level)
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
                    ->after(function () {
                        Notification::make()
                            ->title(__('Plan assigned successfully'))
                            ->success()
                            ->send();
                    }),
                ReplicateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
