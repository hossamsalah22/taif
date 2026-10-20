<?php

namespace App\Filament\Resources\LearningGoals\Schemas;

use App\Enums\PriorityEnum;
use App\Enums\RewardTargetEnum;
use App\Filament\Resources\LearningPlans\Schemas\LearningStructureHint;
use App\Models\LearningPlan;
use App\Models\Question;
use App\Models\Reward;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningGoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                LearningStructureHint::make('goal'),

                Section::make(__('Goal Details'))
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Goal Name'))
                            ->required()
                            ->translatableTabs()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('Goal Description'))
                            ->translatableTabs()
                            ->columnSpanFull(),
                        TextInput::make('acquired_skills')
                            ->label(__('Acquired Skills'))
                            ->helperText(__('What the child will be able to do after finishing this goal, shown to the parent.'))
                            ->translatableTabs()
                            ->columnSpanFull(),
                        Select::make('plans')
                            ->label(__('Learning Plans'))
                            ->relationship('plans', 'name')
                            ->getOptionLabelFromRecordUsing(fn (LearningPlan $record) => $record->name)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText(__('Optional. A goal can be created without a plan and shared between several plans.')),
                        Select::make('display_priority')
                            ->label(__('Display priority'))
                            ->required()
                            ->options(PriorityEnum::options())
                            ->helperText(__('Order inside the plan: 1 comes first.')),
                    ]),

                Section::make(__('Content & Settings'))
                    ->description(__('Add the lessons of this goal, and inside each lesson the exercises the child plays.'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('questions')
                            ->label(__('Questions'))
                            ->relationship('questions', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Question $record) => $record->prompt ?: '#'.$record->id)
                            ->multiple()
                            ->searchable(false)
                            ->preload()
                            ->helperText(__('Optional. Assessment questions linked to this goal.'))
                            ->columnSpanFull(),
                        LessonsRepeater::make()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Access & Reward'))
                    ->icon('heroicon-o-gift')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('reward_id')
                            ->label(__('Goal Completion Reward'))
                            ->options(fn () => Reward::query()
                                ->where('target_type', RewardTargetEnum::GOAL->value)
                                ->get()
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder(__('No reward'))
                            ->helperText(__('Given to the child once every lesson in this goal is completed.')),
                        Toggle::make('is_locked')
                            ->label(__('Locked (Sequential)'))
                            ->helperText(__('Locked goals open only after the previous goal is completed.'))
                            ->inline(false),
                    ]),
            ]);
    }
}
