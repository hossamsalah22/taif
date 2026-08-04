<?php

namespace App\Filament\Resources\Rewards\Schemas;

use App\Enums\RewardTypeEnum;
use App\Models\Reward;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('name'))
                    ->rule('required')
                    ->rule(function (Get $get, ?Reward $record) {
                        return function (string $attribute, $value, $fail) use ($record) {
                            if (! filled($value)) {
                                return;
                            }

                            $query = Reward::query();
                            if ($record?->getKey()) {
                                $query->whereKeyNot($record->getKey());
                            }

                            $exists = $query
                                ->where(function ($q) use ($value) {
                                    $q->where('name->en', $value)
                                        ->orWhere('name->ar', $value);
                                })
                                ->exists();

                            if ($exists) {
                                $fail(__('validation.unique', ['attribute' => __('name')]));
                            }
                        };
                    })
                    ->translatableTabs(),
                Select::make('type')
                    ->label(__('Reinforcement Type'))
                    ->options(RewardTypeEnum::options())
                    ->required()
                    ->live(),
                SpatieMediaLibraryFileUpload::make('sound')
                    ->collection('rewards')
                    ->acceptedFileTypes(['audio/*'])
                    ->visible(fn (Get $get) => $get('type') === RewardTypeEnum::SOUND->value)
                    ->required(fn (Get $get) => $get('type') === RewardTypeEnum::SOUND->value),

                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('rewards')
                    ->acceptedFileTypes(['image/*', 'application/json'])
                    ->visible(fn (Get $get) => $get('type') !== RewardTypeEnum::SOUND->value)
                    ->required(fn (Get $get) => $get('type') !== RewardTypeEnum::SOUND->value),
            ]);
    }
}
