<?php

namespace App\Filament\Resources\LearningGoals\Schemas;

use App\Enums\PriorityEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LearningGoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('learning_plan_id')
                    ->label(__('Learning Plan'))
                    ->relationship('plan', 'name')
                    ->required(),
                Select::make('display_priority')
                    ->label(__('Display priority'))
                    ->required()
                    ->options(PriorityEnum::options()),
                TextInput::make('name')
                    ->label(__('Goal Name'))
                    ->required()
                    ->translatableTabs(),
                Textarea::make('description')
                    ->label(__('Goal Description'))
                    ->translatableTabs(),
                TextInput::make('acquired_skills')
                    ->label(__('Acquired Skills'))
                    ->translatableTabs(),
                Toggle::make('is_locked')
                    ->label(__('Locked (Sequential)')),
            ]);
    }
}
