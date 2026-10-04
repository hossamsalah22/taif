<?php

namespace App\Filament\Resources\LearningLessons\Schemas;

use App\Enums\PriorityEnum;
use App\Enums\RewardTargetEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningLessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Lesson Details'))
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->columnSpanFull()
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
                            ->required(),
                        Select::make('display_priority')
                            ->label(__('Display priority'))
                            ->required()
                            ->options(PriorityEnum::options()),
                    ]),

                Section::make(__('Access & Reward'))
                    ->icon('heroicon-o-gift')
                    ->columns(2)
                    ->columnSpanFull()
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
                            ->placeholder(__('No reward')),
                        Toggle::make('is_locked')
                            ->label(__('Locked'))
                            ->helperText(__('Locked lessons open only after the previous lesson is completed.'))
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
