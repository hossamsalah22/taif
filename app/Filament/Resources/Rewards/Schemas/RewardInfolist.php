<?php

namespace App\Filament\Resources\Rewards\Schemas;

use App\Enums\RewardTargetEnum;
use App\Enums\RewardTypeEnum;
use App\Models\Reward;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RewardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type')
                    ->label(__('Reinforcement Type'))
                    ->formatStateUsing(fn (RewardTypeEnum $state): string => RewardTypeEnum::label($state))
                    ->icon(fn (RewardTypeEnum $state): string => RewardTypeEnum::icon($state))
                    ->color(fn (RewardTypeEnum $state): string => RewardTypeEnum::colors($state))
                    ->badge(),
                TextEntry::make('target_type')
                    ->label(__('Reward Target'))
                    ->formatStateUsing(fn (RewardTargetEnum $state): string => RewardTargetEnum::label($state))
                    ->color(fn (RewardTargetEnum $state): string => RewardTargetEnum::colors($state))
                    ->badge(),
                TextEntry::make('deleted_at')
                    ->label(__('Deleted At'))
                    ->dateTime()
                    ->visible(fn (Reward $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
