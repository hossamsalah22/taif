<?php

namespace App\Filament\Resources\Countries\Schemas;

use App\Models\Country;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('name'))
                    ->rule('required')
                    ->rule(function (Get $get, ?Country $record) {
                        return function (string $attribute, $value, $fail) use ($record) {
                            if (! filled($value)) {
                                return;
                            }

                            $query = Country::query();
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
                TextInput::make('iso')
                    ->label(__('iso'))
                    ->rule(['required', 'max:2'])
                    ->unique(ignoreRecord: true),
                TextInput::make('iso3')
                    ->label(__('iso3'))
                    ->rule('max:3'),
                TextInput::make('numcode')
                    ->label(__('numcode'))
                    ->numeric(),
                TextInput::make('phonecode')
                    ->label(__('phonecode'))
                    ->tel()
                    ->rule('required')
                    ->numeric(),
                TextInput::make('currency_code')
                    ->label(__('currency_code'))
                    ->rule('max:3'),
            ]);
    }
}
