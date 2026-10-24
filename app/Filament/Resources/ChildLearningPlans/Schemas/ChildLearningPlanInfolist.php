<?php

namespace App\Filament\Resources\ChildLearningPlans\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\ChildLearningPlanStatusEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChildLearningPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Assigned Plan Summary'))
                    ->schema([
                        TextEntry::make('child.name')
                            ->label(__('Child Name')),
                        TextEntry::make('learningPlan.name')
                            ->label(__('Assigned Plan Title')),
                        TextEntry::make('child.autism_level')
                            ->label(__('Severity Alignment'))
                            ->formatStateUsing(fn (AutismLevelEnum $state) => AutismLevelEnum::label($state))
                            ->color(fn (AutismLevelEnum $state) => AutismLevelEnum::color($state))
                            ->badge(),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->formatStateUsing(fn (ChildLearningPlanStatusEnum $state) => ChildLearningPlanStatusEnum::label($state))
                            ->color(fn (ChildLearningPlanStatusEnum $state) => ChildLearningPlanStatusEnum::color($state))
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label(__('Assigned At'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('Last Updated'))
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
