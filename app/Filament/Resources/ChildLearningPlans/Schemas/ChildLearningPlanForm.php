<?php

namespace App\Filament\Resources\ChildLearningPlans\Schemas;

use App\Enums\ChildLearningPlanStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ChildLearningPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('child_id')
                    ->label(__('Child'))
                    ->required()
                    ->relationship('child', 'name'),
                Select::make('learning_plan_id')
                    ->label(__('Learning Plan'))
                    ->required()
                    ->relationship('learningPlan', 'name'),
                Select::make('status')
                    ->label(__('Status'))
                    ->options(ChildLearningPlanStatusEnum::options())
                    ->required(),
            ]);
    }
}
