<?php

namespace App\Filament\Resources\ChildLearningPlans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChildLearningPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('child_id')
                    ->required()
                    ->numeric(),
                TextInput::make('learning_plan_id')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['in_progress' => 'In progress', 'completed' => 'Completed', 'archived' => 'Archived'])
                    ->default('in_progress')
                    ->required(),
            ]);
    }
}
