<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Models\Admin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('personal_info'))->schema([
                    TextInput::make('name')
                        ->label(__('name'))
                        ->rule(['required', 'max:255']),
                    TextInput::make('email')
                        ->label(__('email'))
                        ->rule(['required', 'max:255', 'email'])
                        ->unique(ignoreRecord: true),
                    Select::make('country_code')
                        ->label(__('country_code'))
                        ->relationship('country', 'name')
                        ->searchable()
                        ->preload()
                        ->rule(['required', 'max:2'])
                        ->default('SA')
                        ->live()
                        ->afterStateUpdated(
                            fn ($state, Set $set, Get $get) => $set('phone', $get('phone'))
                        ),
                    TextInput::make('phone')
                        ->label(__('phone'))
                        ->tel()
                        ->rule(['required', 'max:255'])
                        ->live()
                        ->rules(fn (Get $get, ?Admin $record): array => [
                            'required',
                            'phone_number:'.($get('country_code') ?? 'SA'),
                            function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                $formattedPhone = prepare_phone($value, $get('country_code') ?? 'SA');

                                $exists = Admin::where('phone', $formattedPhone)
                                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                    ->exists();

                                if ($exists) {
                                    $fail(__('validation.unique', ['attribute' => __('phone')]));
                                }
                            },
                        ])
                        ->dehydrateStateUsing(
                            fn ($state, Get $get) => prepare_phone($state, $get('country_code'))
                        ),

                ])->columns(2),
                Select::make('roles')
                    ->label(__('roles'))
                    ->relationship('roles', 'name', fn (Builder $query) => $query->where('guard_name', 'web'))
                    ->rule('required')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
