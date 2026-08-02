<?php

namespace App\Filament\Resources\LearningGoals\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LearningGoalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('plan.name')
                    ->label(__('Learning Plan')),
                TextEntry::make('name')
                    ->label(__('Goal Name')),
                TextEntry::make('description')
                    ->label(__('Goal Description'))
                    ->placeholder('-'),
                TextEntry::make('acquired_skills')
                    ->label(__('Acquired Skills'))
                    ->placeholder('-'),
                IconEntry::make('is_locked')
                    ->label(__('Locked'))
                    ->boolean(),
                TextEntry::make('display_priority')
                    ->label(__('Display Priority'))
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
