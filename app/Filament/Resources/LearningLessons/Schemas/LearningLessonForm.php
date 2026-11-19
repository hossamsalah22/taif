<?php

namespace App\Filament\Resources\LearningLessons\Schemas;

use App\Enums\PriorityEnum;
use App\Enums\RewardTargetEnum;
use App\Filament\Resources\LearningPlans\Schemas\LearningStructureHint;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class LearningLessonForm
{
    /**
     * The lesson split into tabs, like the plan, goal and reward forms.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                LearningStructureHint::make('lesson'),

                Tabs::make('lesson')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(__('Lesson Details'))
                            ->icon('heroicon-o-academic-cap')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Lesson Name'))
                                    ->required()
                                    ->translatableTabs()
                                    ->columnSpanFull(),
                                Select::make('learning_goal_id')
                                    ->label(__('Learning Goal'))
                                    ->relationship('goal', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The goal this lesson belongs to.'))
                                    ->required(),
                                Select::make('display_priority')
                                    ->label(__('Display priority'))
                                    ->required()
                                    ->options(PriorityEnum::options())
                                    ->helperText(__('Order inside the goal: 1 comes first.')),
                            ]),

                        Tab::make(__('Exercises'))
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                Text::make(__('The exercises the child plays in this lesson. Drag them into the order the child follows.')),
                                ExercisesRepeater::make(),
                            ]),

                        Tab::make(__('Access & Reward'))
                            ->icon('heroicon-o-gift')
                            ->columns(2)
                            ->schema([
                                Select::make('reward_id')
                                    ->label(__('Reward'))
                                    ->relationship(
                                        'reward',
                                        'name',
                                        fn ($query) => $query->where('target_type', RewardTargetEnum::LESSON->value),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder(__('No reward'))
                                    ->helperText(__('Given to the child once every exercise in this lesson is completed.')),
                                Toggle::make('is_locked')
                                    ->label(__('Locked'))
                                    ->helperText(__('Locked lessons open only after the previous lesson is completed.'))
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }
}
