<?php

namespace App\Filament\Resources\Rewards\Schemas;

use App\Models\Reward;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RewardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Reward $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
