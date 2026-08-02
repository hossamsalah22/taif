<?php

namespace App\Filament\Resources\LearningLessons\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LearningLessonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('goal.name')
                    ->label(__('Learning Goal')),
                TextEntry::make('name')
                    ->label(__('Lesson Name')),
                TextEntry::make('reward.name')
                    ->label(__('Reward'))
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
