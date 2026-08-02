<?php

namespace App\Filament\Resources\LearningLessons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LearningLessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('learning_goal_id')
                    ->label(__('Learning Goal'))
                    ->relationship('goal', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label(__('Lesson Name'))
                    ->required()
                    ->translatableTabs(),
                Select::make('reward_id')
                    ->label(__('Reward'))
                    ->relationship('reward', 'name'),
                Toggle::make('is_locked')
                    ->label(__('Locked')),
                TextInput::make('display_priority')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
